<?php

/*
 * API
 *
 * This interfaces with cached indices to easily
 * retrieve data for a request.
 */
class API {
  
  // A reference to the site's cache.
  protected static $cache;
  
  // The locations of cached index files.
  protected static $index = [
    'environment' => CONFIG['engine']['cache']['index'].'/environment.php',
    'site' => CONFIG['engine']['cache']['index'].'/site.php',
    'assets' => CONFIG['engine']['cache']['index'].'/assets.php',
    'patterns' => CONFIG['engine']['cache']['index'].'/patterns.php',
    'partials' => CONFIG['engine']['cache']['index'].'/partials.php',
    'routes' => CONFIG['engine']['cache']['index'].'/routes.php',
    'endpoints' => CONFIG['engine']['cache']['index'].'/endpoints.php',
    'helpers' => CONFIG['engine']['cache']['index'].'/helpers.php',
  ];
  
  // Register known API endpoints and their respective methods.
  protected static $endpoints = [
    'GET' => [
      '/endpoint'   => 'API::getEndpoint',
      '/asset'      => 'API::getAsset',
      '/error'      => 'API::getError',
      '/partials'   => 'API::getPartials',
      '/helpers'    => 'API::getHelpers',
    ]
  ];
  
  // Define flags that can be used for the `GET` pattern method.
  const PATTERN_DATA = 0;
  const PATTERN_GROUPS = 1;
  
  // Construct the API.
  function __construct( ) {
    
    // Get the cache root path, and site's domain.
    $cache = CONFIG['engine']['cache']['root'];
    $domain = CONFIG['__site__']['domain'];
    
    // Initialize the cache.
    self::$cache = new Cache($cache.'/'.$domain.'.php');
    
  }
  
  // Call the appropriate method to process the incoming API request.
  function __call( $method, $arguments ) {
    
    // Verify that a static method by the same name exists.
    if( !method_exists(static::class, $method) ) {
      
      // Get a reflection of the method trying to be called.
      $reflection = new ReflectionMethod(__CLASS__, $method);
      
      // Verify that the static method is public, or throw an error otherwise.
      if( !$reflection::isPublic() ) {
      
        // Throw an error.
        return new Error("Call to undefined method ".__CLASS__."::$method");
        
      }
      
    }
    
    // Forward the request to the static method.
    return call_user_func_array(__CLASS__."::$method", $arguments);
    
  }
  
  
  //*********** PROTECTED METHODS ***********//
  
  // Ensure that some index data existing within the cache and is up-to-date.
  protected static function ensure( string $index ) {
    
    // Check to see if the necessary index data exists within the cache.
    $cached = self::$cache->get($index);
    
    // If index data has not yet been cached or is outdated, then (re)cache it now, and return the cached data.
    if( !isset($cached) or self::outdated($index) ) return self::cache($index);
    
    // Otherwise, return the cached data.
    return $cached;
    
  }
  
  // Cache some index data given the name of the target index.
  protected static function cache( string $index ) {
    
    // Get the cached index data.
    $data = (include self::$index[$index]);
    
    // Verify that the data was included.
    if( $data !== false ) {
    
      // Cache the data, then return it.
      if( self::$cache->set($index, $data) ) return self::$cache->get($index);

      // Otherwise, throw an error if the data could not be cached.
      else throw new Error("Failed to cache $index data");
      
    }
    
    // Otherwise, throw an error if there was no data to cache.
    else throw new Erorr("Index $index not available");
    
  }
  
  // Determine if some data within the cache is outdated and needs recaching.
  protected static function outdated( string $index ) {
    
    // Get the index's last modified date from the cache.
    $cached = self::$cache->get("$index.modified");
    
    // Convert the index's last modified time to a timestamp.
    if( is_a($cached, 'DateTime') ) $cached = $cached->getTimestamp();
    
    // Get the index file's last modified time.
    $file = Cache::modified(self::$index[$index]);
    
    // Determine if the cached data is outdated by seeing if the index file's last modified if newer.
    return $cached < $file;
    
  }
  
  // Parse some data about a request.
  protected static function parse( string $method, string $endpoint ) {
    
    // Parse the request.
    $request = Request::parse($method, $endpoint);
    
    // Replace URL with an API URL.
    $request['url'] = str_replace($request['endpoint'], '', $request['url']).'/api'.$request['endpoint'];
    
    // Initialize the request's API data.
    $request['api'] = [];
    
    // Determine the request's API endpoint.
    $request['api']['endpoint'] = array_last(array_values(array_filter(array_keys(self::$endpoints[$method]), function($endpoint) use ($request) {
      
      // Find the requested endpoint.
      return str_starts_with($request['endpoint'], $endpoint);
      
    })));
    
    // Get the request's path within the API endpoint.
    $request['api']['path'] = preg_replace('/^'.str_replace('/', '\/', preg_quote($request['api']['endpoint'])).'/', '', $request['endpoint']);
    
    // Return the parsed request data.
    return $request;
    
  }
  
  //*********** GET METHODS ***********//
  
  /* Get some indexed data from the cache. This works
   * by registering various endpoints that can be "queried"
   * to retrieve index data from within the cache.
   *
   * @example /endpoint/ - Retrieves endpoint data for a page at `/`.
   * @example /asset/css/style.css - Retrieves asset data for asset `css/style.css`.
   */
  public static function get( string $endpoint ) {

    // Parse the request.
    $request = self::parse('GET', $endpoint);
    
    // Then, forward the request to the API endpoint's appropriate method for processing.
    return self::$endpoints['GET'][$request['api']['endpoint']]($request['api']['path']);
    
  }
  
  // Derive some endpoint data from the cached index data.
  protected static function getEndpoint( string $path ) {
    
    // Immediately detect error endpoints, and reroute the request.
    if( Route::isError($path) ) return self::getError((int) basename($path));
    
    // Immediately detect asset endpoints, and reroute the request.
    if( Route::isAsset($path) ) return self::getAsset($path);
    
    // Ensure that endpoint data exists within the cache.
    $endpoints = self::ensure('endpoints');

    // Attempt to retrieve the page data for the endpoint from the cache.
    $endpoint = self::$cache->get("pages.$path");

    // If the endpoint was not found, then check to see if relevant index data has been cached.
    if( !isset($endpoint) ) {
      
      // Find the endpoint for the given path.
      $endpoint = array_get(array_values(array_filter($endpoints['data'], function($endpoint) use ($path) {
        
        // Find the endpoint data for the given endpoint path.
        return (is_array($endpoint->endpoint) ? in_array($path, $endpoint->endpoint) : $endpoint->endpoint == $path);
        
      })), 0);
      
      // If the endpoint doesn't exist, then return a 404 error page instead.
      if( !isset($endpoint) ) return self::getError(404);
      
      // If the endpoint does not have a template pattern, then return a 515 error page instead.
      if( !isset($endpoint->pattern) ) return self::getError(515);
        
      // Cache the endpoint, or throw an error if caching fails.
      if( !self::$cache->set("pages.$path", $endpoint) ) throw new Error("Failed to cache endpoint $path");

    }
    
    // Return the endpoint.
    return (is_array($endpoint) ? $endpoint['data'] : $endpoint);
    
  }
  
  // Derive some asset data from the cached index data.
  protected static function getAsset( string $path ) {
    
    // Ensure that endpoint data exists within the cache.
    $endpoints = self::ensure('endpoints');
    
    // Attempt to retrieve the asset data from the cache.
    $asset = self::$cache->get("assets.$path");
    
    // If the asset was not found, either use the given endpoint or retrieve one from the index.
    if( !isset($asset) ) {
      
      // Get the endpoints for assets only.
      $assets = array_values(array_filter($endpoints['data'], function($endpoint) {
        
        // Locate all asset endpoints.
        return $endpoint->asset;
        
      }));
      
      // Find the endpoint for the given path.
      $asset = array_get(array_values(array_filter($assets, function($endpoint) use ($path) {
        
        // Find the endpoint data for the given endpoint path.
        return (is_array($endpoint->endpoint) ? in_array($path, $endpoint->endpoint) : $endpoint->endpoint == $path);
        
      })), 0);
      
      // If the endpoint doesn't exist, then return a 404 error page instead.
      if( !isset($asset) ) return self::getError(404);
      
      // Cache the asset, or throw an error if caching fails.
      if( !self::$cache->set("assets.$path", $asset) ) throw new Error("Failed to cache asset $path");
      
    }
    
    // Return the asset.
    return (is_array($asset) ? $asset['data'] : $asset);
    
  }
  
  // Derive some error data from cached index data.
  protected static function getError( $code ) {
    
    // Ensure that endpoint data exists within the cache.
    $endpoints = self::ensure('endpoints');
    
    // Make sure the error code is an integer.
    $code = is_int($code) ? $code : (int) trim($code, '/');
    
    // Attempt to retrieve the error data from the cache.
    $error = self::$cache->get("errors.$code");
    
    // If the error was not found, either use the given endpoint or retrieve one from the index.
    if( !isset($error) ) {
      
      // Get the endpoints for errors only.
      $errors = array_values(array_filter($endpoints['data'], function($endpoint) {
        
        // Locate all asset endpoints.
        return ($endpoint->error !== false);
        
      }));
      
      // Find the endpoint for the given error code.
      $error = array_get(array_values(array_filter($errors, function($endpoint) use ($code) {
        
        // Find the endpoint data for the given error code.
        return ($endpoint->error == $code);
        
      })), 0);
      
      // If the endpoint doesn't exists, then return a 404 error page instead.
      if( !isset($error) ) return self::getError(404);
      
      // Cache the error, or throw an error if caching fails.
      if( !self::$cache->set("errors.$code", $error) ) throw new Error("Failed to cache error $code");
      
    }
    
    // Return the error.
    return (is_array($error) ? $error['data'] : $error);
    
  }
  
  // Derive partial data from cached index data.
  protected static function getPartials() {
    
    // Ensure that partial data exists within the cache.
    $partials = self::ensure('partials');
    
    // Then, return the partial data.
    return $partials['data'];
    
  }
  
  // Derive helper data from cached index data.
  protected static function getHelpers() {
    
    // Ensure that helper data exists within the cache.
    $helpers = self::ensure('helpers');
    
    // Then, return the helper data.
    return $helpers['data'];
    
  }
  
}

?>