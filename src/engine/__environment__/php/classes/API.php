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
    'routes' => CONFIG['engine']['cache']['index'].'/routes.php'
  ];
  
  // Register methods and processers.
  protected static $methods = [
    'GET' => [
      'endpoint'  => 'API::getEndpoint',
      'asset'     => 'API::getAsset',
      'error'     => 'API::getError',
      'pattern'   => 'API::getPattern',
      'partials'  => 'API::getPartials'
    ]
  ];
  
  // Defines flags that can be used for the merge method.
  const MERGE_PATHS = 1;
  const MERGE_CONTENTS = 2;
  const MERGE_NORMAL = 4;
  const MERGE_RECURSIVE = 8;
  const MERGE_KEYED = 16;
  const MERGE_GROUPED = 32;
  const MERGE_OVERRIDE = 64;
  
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
    $file = Cache::modified($index);
    
    // Determine if the cached data is outdated by seeing if the index file's last modified if newer.
    return $cached < $file;
    
  }
  
  // Merge data files.
  protected static function merge( ...$arrays/*, $flags = API::MERGE_KEYED | API::MERGE_RECURSIVE*/ ) {

    // Get flags, or set the default.
    $flags = !is_array(array_last($arrays)) ? array_last($arrays) : API::MERGE_KEYED | API::MERGE_RECURSIVE;
    
    // Filter out any non-arrays from the data set.
    $arrays = array_values(array_filter($arrays, 'is_array'));
    
    // Merge the data on keys, where keys are composed of to data file IDs.
    if( $flags & API::MERGE_KEYED ) {
      
      // Initialize the result.
      $result = [];
      
      // Merge the data arrays.
      foreach( $arrays as $data ) {
      
        // Merge data by key.
        foreach( $data as $file => $content ) {

          // Derive the key from the file's ID.
          $key = File::id($file);

          // Get the existing data for that key.
          $existing = array_get($result, $key, []);

          // Group the data by key.
          if( $flags & API::MERGE_GROUPED ) {

            // Add the data into the group.
            $result = array_set($result, $key, array_merge([], $existing, [$content->data]));

          }

          // Recursively merge the data into the keyed data.
          else if( $flags & API::MERGE_RECURSIVE ) {

            // Recursively merge the data.
            $result = array_set($result, $key, array_merge_recursive($existing, $content->data));

          }

          // Otherwise, merge the data into the keyed data.
          else if( $flags & API::MERGE_NORMAL ) {

            // Merge that data normally.
            $result = array_set($result, $key, array_merge($existing, $content->data));

          }

          // Otherwise, set and/or override keyed data.
          else $result = array_set($result, $key, $content->data, ($flags & API::MERGE_OVERRIDE));

        }
        
      }
      
      // Return the result.
      return $result;
      
    }
  
    // Recursively merge the data, and return it. 
    if( $flags & API::MERGE_RECURSIVE ) return array_merge_recursive(...array_values($path));
    
    // Otherwise, merge the data, and return it.
    if( $flags & API::MERGE_NORMAL ) return array_merge(...array_values($path));
    
    // Otherwise, return only the data contents.
    if( $flags & API::MERGE_CONTENTS ) return array_values($path);
    
    // Otherwise, return the data as is with paths included.
    return $path;
    
  }
  
  // Compile the meta data set for a request.
  protected static function compile( array $environment, array $site, array $endpoint ) {
   
    // Get global, meta, and shared data.
    $global = self::merge($environment['global'], $site['global'], API::MERGE_KEYED | API::MERGE_RECURSIVE);
    $meta = self::merge($environment['meta'], $site['meta'], API::MERGE_KEYED | API::MERGE_RECURSIVE);
    $shared = self::merge($environment['shared'], $site['shared'], API::MERGE_KEYED | API::MERGE_GROUPED);
    
    // Merge additional data into the route's endpoint data.
    $data = array_merge($endpoint, [
      '__global__' => $global,
      '__meta__' => $meta,
      '__shared__' => $shared,
      '__params__' => Request::params()
    ]);
    
    // Return the compiled data.
    return $data;
    
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
    
    // Get data about the request.
    $request = Request::parse('GET', $endpoint);
    
    // Detect ending slashes on the endpoint.
    $slash = str_ends_with($endpoint, '/');
    
    // Extract the name of the API's internal process that's being requested.
    $process = ($parts = explode('/', trim($request['endpoint'], '/')))[0];
    
    // Make sure the process exists, or indicate that the request failed otherwise.
    if( !array_key_exists($process, self::$methods['GET']) ) return false;
    
    // Get the path within the index that the request is wanting to reach.
    $path = cleanpath('/'.implode('/', array_subset($parts, 0, ARRAY_SUBSET_EXCLUDE)).($slash ? '/': ''));
    
    // Otherwise, return the result of the process.
    return self::$methods['GET'][$process]($path, $request);
    
  }
  
  // Derive some endpoint data from the cached index data.
  protected static function getEndpoint( string $path, array $request ) {
    
    // Immediately detect error endpoints, and reroute them.
    if( Route::isError($path) ) return self::getError((int) basename($path));
    
    // Immediately detect asset endpoints, and reroute them.
    if( Route::isAsset($path) ) return self::getAsset($path);

    // Attempt to retrieve the endpoint data from the cache.
    $endpoint = self::$cache->get("endpoints.$path");

    // If the endpoint was not found, then check to see if relevant index data has been cached.
    if( !isset($endpoint) ) {

      // Check to see if the necessary index data exists within the cache.
      $environment = self::$cache->get('environment');
      $site = self::$cache->get('site');
      $patterns = self::$cache->get('patterns');
      $routes = self::$cache->get('routes');

      // If any index data has not yet been cached or is outdated, then (re)cache it now, and get it.
      if( !isset($environment) or self::outdated('environment') ) $environment = self::cache('environment');
      if( !isset($site) or self::outdated('site') ) $site = self::cache('site');
      if( !isset($patterns) or self::outdated('patterns') ) $patterns = self::cache('patterns');
      if( !isset($routes) or self::outdated('routes') ) $routes = self::cache('routes');
      
      // Get the index data.
      $environment = $environment['data'];
      $site = $site['data'];
      $patterns = $patterns['data'];
      $routes = $routes['data'];
      
      // Find the route for the given path within the cached index data.
      $route = array_values(array_filter($routes, function($route) use ($path) {

        // If endpoints are an array, look inside the array for matching endpoints.
        if( is_array($route->endpoint) ) return in_array($path, $route->endpoint);
        
        // Look for a route with a matching endpoint.
        return $route->endpoint == $path;
        
      }));

      // Verify that a route exists for the endpoint, or return an error otherwise.
      if( !isset($route[0]) ) return self::getError(404);
      
      // Capture the route.
      $route = $route[0];

      // Check to see if the route points to an asset, and if so, treat it as such.
      if( $route->asset !== false ) return self::getAsset($path);
      
      // Check to see if the route points to an error, and if so, treat it as such.
      if( $route->error !== false ) return self::getError((int) $route->id);

      // Get the data for the endpoint.
      // FIXME: Should/will all endpoints have a data file? If not, this may need to add an `isset` check to determine if some page data for the route exists, and if none exists, generate an empty `Data` object as needed.
      $data = $site['site'][$route->path];
      
      // Get the endpoint's template page type.
      $pageType = $data->data['template'];
      
      // Lookup the endpoint's template pattern by page type.
      $template = array_values(array_filter($patterns['templates'], function($pattern) use ($pageType) {
        
        // Find the template with the matching page type, PLID, or ID.
        return ($pattern->pageType == $pageType or $pattern->plid == $pageType or $pattern->id == $pageType);
        
      }));
      
      // Verify that the template exists, or return an error otherwise.
      if( !isset($template[0]) ) return self::getError(515);
      
      // Capture the template pattern.
      $template = $template[0];
      
      // Compile the data for the endpoint.
      $data = self::compile($environment, $site, $data->data);
      
      // Convert the route to an endpoint.
      $endpoint = new Endpoint($route, $data, $template);
      
      // Cache the endpoint, or throw an error if caching failed.
      if( !self::$cache->set("endpoints.$path", $endpoint) ) {
        
        // Throw an error.
        throw new Error("Failed to cache endpoint $path");
          
      };

    }

    // Return the endpoint.
    return $endpoint;
    
  }
  
  // Derive some asset data from the cached index data.
  protected static function getAsset( string $path ) {
    
    // Attempt to retrieve the asset data from the cache.
    $asset = self::$cache->get("assets.$path");
    
    // If the asset was not found, then check to see if relevant index data has been cached.
    if( !isset($asset) ) {

      // Check to see if the necessary index data exists within the cache.
      $assets = self::$cache->get('assets');
      $routes = self::$cache->get('routes');

      // If any index data has not yet been cached or is outdated, then (re)cache it now, and get it.
      if( !isset($assets) or self::outdated('assets') ) $assets = self::cache('assets');
      if( !isset($routes) or self::outdated('routes') ) $routes = self::cache('routes');
      
      // Get the index data.
      $assets = $assets['data'];
      $routes = $routes['data'];
      
      // Find the route for the given path within the cached index data.
      $route = array_values(array_filter($routes, function($route) use ($path) {
        
        // If endpoints are an array, look inside the array for matching endpoints.
        if( is_array($route->endpoint) ) return in_array($path, $route->endpoint);
        
        // Look for a route with a matching endpoint.
        return $route->endpoint == $path;
        
      }));

      // Verify that a route exists for the asset, or return an error otherwise.
      if( !isset($route[0]) ) return self::getError(404);
      
      // Capture the route.
      $route = $route[0];

      // Get the data for the asset.
      $data = $assets[$route->path];
      
      // Convert the asset to an endpoint.
      $asset = new Endpoint($route, object_to_array($data), new Pattern(['template' => true]));
      
      // Cache the asset, or throw an error if caching failed.
      if( !self::$cache->set("assets.$path", $asset) ) {
        
        // Throw an error.
        throw new Error("Failed to cache asset $path");
          
      };

    }

    // Return the asset.
    return $asset;
    
  }
  
  // Derive some error data from cached index data.
  protected static function getError( $code ) {
    
    // Convert the error code to an integer.
    $code = is_int($code) ? $code : (int) trim($code, '/');
    
    // Attempt to retrieve the error data from the cache.
    $error = self::$cache->get("errors.$code");
    
    // If the error was not found, then check to see if relevant index data has been cached.
    if( !isset($error) ) {
      
      // Check to see if the necessary index data exists within the cache.
      $environment = self::$cache->get('environment');
      $site = self::$cache->get('site');
      $patterns = self::$cache->get('patterns');
      $routes = self::$cache->get('routes');

      // If any index data has not yet been cached or is outdated, then (re)cache it now, and get it.
      if( !isset($environment) or self::outdated('environment') ) $environment = self::cache('environment');
      if( !isset($site) or self::outdated('site') ) $site = self::cache('site');
      if( !isset($patterns) or self::outdated('patterns') ) $patterns = self::cache('patterns');
      if( !isset($routes) or self::outdated('routes') ) $routes = self::cache('routes');
      
      // Get the index data.
      $environment = $environment['data'];
      $site = $site['data'];
      $patterns = $patterns['data'];
      $routes = $routes['data'];
    
      // Find the route for the given error within the cached index data.
      $route = array_values(array_filter($routes, function($route) use ($code) {
        
        // If endpoints are an array, look inside the array for matching endpoints.
        if( is_array($route->endpoint) ) return in_array($code, $route->endpoint);
        
        // Look for a route with a matching endpoint.
        return $route->endpoint == "/$code";
        
      }));

      // Verify that a route exists for the error, or simulate one.
      $route = isset($route[0]) ? $route[0] : new Route([
        'id' => $code,
        'endpoint' => "/$code",
      ]);
      
      // Get the data for the route, or simulate some.
      $data = (isset($route->path) and isset($site['site'][$route->path])) ? $site['site'][$route->path] : new Data(array_merge([
        'code' => $code
      ], CONFIG['errors'][$code]));
      
      // Get the endpoint's template page type, if possible.
      $pageType = isset($data->data['template']) ? $data->data['template'] : false;
      
      // Lookup the endpoint's template by page type, if found.
      if( $pageType ) {
        
        // Lookup the template.
        $template = array_values(array_filter($patterns['templates'], function($pattern) use ($pageType) {
        
          // Find the template with the matching page type, PLID, or ID.
          return ($pattern->pageType == $pageType or $pattern->plid == $pageType or $pattern->id == $pageType);

        }));
      
        // Use the given template if a page type exists.
        if( isset($template[0]) ) $template = $template[0];
        
        // Otherwise, throw a different error, or use the default error template.
        else {
          
          // If the error code is not the default for undefined templates, then return that error.
          if( $code != 515 ) return self::getError(515);
          
          // Otherwise, create a template on the fly.
          else $template = new Pattern([
            'template' => true,
            'pattern' => CONFIG['defaults']['errorTemplate']
          ]);
          
        }
        
      }
      
      // Otherwise, throw a different error, or create a template on the fly as needed.
      else {

        // If the error code is not the default for undefined templates, then return that error.
        if( $code != 515 ) return self::getError(515);

        // Otherwise, create a template on the fly.
        else $template = new Pattern([
          'template' => true,
          'pattern' => CONFIG['defaults']['errorTemplate']
        ]);

      }
      
      // Compile the data for the endpoint.
      $data = self::compile($environment, $site, $data->data);
      
      // Convert the error to an endpoint.
      $error = new Endpoint($route, $data, $template);
      
      // Cache the endpoint, or throw an error if caching failed.
      if( !self::$cache->set("errors.$code", $error) ) {
        
        // Throw an error.
        throw new Error("Failed to cache error $code");
          
      };
      
    }
    
    // Return the error.
    return $error;
    
  }
  
  // Derive pattern data from cached index data.
  protected static function getPattern( string $path = null ) {
    
    // Get the pattern ID.
    $id = isset($path) ? trim($path, '/') : null;
    
    // Verify that an actual ID was set, or use null by default.
    if( $id == '' ) $id = null;
    
    // Attempt to retrieve patterns from the cache.
    $patterns = self::$cache->get('patterns');
    
    // If patterns have not been cached yet or are outdated, then (re)cache them now, and get them.
    if( !isset($patterns) or self::outdated('patterns') ) $patterns = self::cache('patterns');
    
    // Get the pattern data.
    $patterns = $patterns['data'];

    // Return the given pattern if a pattern ID was given.
    if( isset($id) ) {
      
      // Get the pattern ID parts.
      $parts = Pattern::parse($path);
      
      // Get the pattern group.
      $group = $patterns[$parts['group']['name']];
      
      // Filter the pattern group for the pattern.
      $pattern = array_values(array_filter($group, function($pattern) use ($id) {
        
        // Find the pattern with the given PLID or ID.
        return $pattern->plid == $id or $pattern->id == $id;
        
      }));
      
      // Get the pattern, or nothing otherwise.
      $pattern = isset($pattern[0]) ? $pattern[0] : null;
      
    }
    
    // Otherwise, return all patterns otherwise.
    return array_merge(...array_values($patterns));
    
  }
  
  // Derive partial data from cached index data.
  protected static function getPartials() {
    
    // Attempt to retrieve partials from the cache.
    $partials = self::$cache->get('partials');
    
    // If partials were not found, build partials.
    if( !isset($partials) ) {
    
      // Get all patterns.
      $patterns = API::getPattern('/');
    
      // Convert the patterns to partials.
      $partials = array_reduce($patterns, function($result, $pattern) {
        
        // Save the partial by its PLID.
        $result[$pattern->plid] = $pattern->pattern;
        
        // Alias the partial by its ID and include path.
        $result[$pattern->id] = &$result[$pattern->plid];
        $result[trim($pattern->path, '/')] = &$result[$pattern->plid];
        
        // Continue building partials.
        return $result;
        
      }, []);
      
      // Cache the partials, or throw an error if the partials could not be cached.
      if( !self::$cache->set('partials', $partials) ) {
        
        // Throw an error.
        throw new Error("Failed to cache partials");
      
      }
      
    }
    
    // Return the partials.
    return $partials;
    
  }
  
}

?>