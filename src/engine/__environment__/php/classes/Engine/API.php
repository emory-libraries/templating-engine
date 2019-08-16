<?php

// Set the namespace.
namespace Engine;

// Use dependencies.
use Cache;
use Request;
use Route;
use Failure;
use Index\API as IndexAPI;
use Engine\API as EngineAPI;

/*
 * API
 *
 * This interfaces with cached indices to easily
 * retrieve data for a request.
 */
class API {

  // A reference to the site's cache.
  protected static $cache;

  // The index location.
  protected static $index = CONFIG['engine']['cache']['index'];

  // Register known API endpoints and their respective methods.
  protected static $endpoints = [
    'GET' => [
      '/endpoint'   => 'Engine\API::getEndpoint',
      '/asset'      => 'Engine\API::getAsset',
      '/error'      => 'Engine\API::getError',
      '/partials'   => 'Engine\API::getPartials',
      '/helpers'    => 'Engine\API::getHelpers',
    ]
  ];

  // Define flags that can be used for the `GET` pattern method.
  const PATTERN_DATA = 0;
  const PATTERN_GROUPS = 1;

  // Construct the API.
  function __construct( ) {

    // Initialize the API.
    static::init();

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

  // Initialize the API if not already initialized.
  protected static function init() {

    // If the cache has not yet been initialized, then initialize it now.
    if( !isset(static::$cache) ) static::$cache = new Cache(CONFIG['engine']['cache']['cache']);

  }

  // Parse some data about a request.
  protected static function parse( string $method, string $endpoint ) {

    // Parse the request.
    $request = Request::parse($method, $endpoint);

    // Replace URL with an API URL.
    $request['url'] = str_replace($request['endpoint'], '', $request['url']).'/api'.$request['endpoint'];

    // Initialize the request's API data.
    $request['api'] = [];

    // Get a list of all endpoint methods.
    $methods = array_keys(static::$endpoints[$method]);

    // Determine the request's API endpoint.
    $request['api']['endpoint'] = array_last(array_values(array_filter($methods, function($endpoint) use ($request) {

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

    // Initialize the cache if not previously initialized.
    static::init();

    // Parse the request.
    $request = static::parse('GET', $endpoint);

    // Then, forward the request to the API endpoint's appropriate method for processing.
    return static::$endpoints['GET'][$request['api']['endpoint']]($request['api']['path']);

  }

  // Derive some endpoint data from the cached index data.
  protected static function getEndpoint( string $path ) {

    // If the path ends with a slash, then assume it's for an index page.
    if( str_ends_with($path, '/') ) $path .= 'index';

    // Get the endpoint's relative source path without any preceding directory separators.
    $src = ltrim($path, '/');

    // Immediately detect error endpoints, and reroute the request.
    if( Route::isError($path) ) return static::getError((int) basename($path));

    // Immediately detect asset endpoints, and reroute the request.
    if( Route::isAsset($path) ) return static::getAsset($path);

    // Get the endpoint data from the cache if available.
    $endpoint = static::$cache->get("endpoints/$src");

    // Get the endpoint's index data path.
    $index = static::$index."/endpoints/$src.php";

    // Get the last cached time of the index data.
    $cached = isset($endpoint) ? File::modified(static::$cache->resolve("endpoints/$src")) : -1;
    $indexed = Cache::exists($index) ? Cache::modified($index) : -1;

    // If no endpoint data was found, or if the endpoint is outdated, then cache it now.
    if( !isset($endpoint) or $cached < $indexed ) {

      // If some index data exists for the endpoint, then use it.
      if( $indexed > -1 ) {

        // Get the endpoint's index data.
        $endpoint = Cache::include($index);

        // Save the endpoint's index data to the cache.
        static::$cache->set("endpoints/$src", $endpoint);

      }

      // Otherwise, return a 404 error page instead.
      else return static::getError(404);

    }

    // Get the endpoint data.
    $endpoint = $endpoint['data'];

    // If the endpoint doesn't have a template pattern, then return a 515 error page instead.
    if( !isset($endpoint->pattern) ) return static::getError(515);

    // Return the endpoint data.
    return $endpoint;

  }

  // Derive some asset data from the cached index data.
  protected static function getAsset( string $path ) {

    // Get the endpoint's relative source path without any preceding directory separators.
    $src = ltrim($path, '/');

    // Get the asset data from the cache if available.
    $asset = static::$cache->get("assets/$src");

    // Get the asset's index data path.
    $index = static::$index."/assets/$src.php";

    // Get the last cached time of the index data.
    $cached = isset($asset) ? File::modified(static::$cache->resolve("assets/$src")) : -1;
    $indexed = Cache::exists($index) ? Cache::modified($index) : -1;

    // If no asset data was found, or if the asset is outdated, then cache it now.
    if( !isset($asset) or $cached < $indexed ) {

      // If some index data exists for the endpoint, then use it.
      if( $indexed > -1 ) {

        // Get the $error's index data.
        $asset = Cache::include($index);

        // Save the asset's index data to the cache.
        static::$cache->set("assets/$src", $asset);

      }

      // Otherwise, return a 404 error page instead.
      else return static::getError(404);

    }

    // Get the asset data.
    $asset = $asset['data'];

    // Return the asset data.
    return $asset;

  }

  // Derive some error data from cached index data.
  protected static function getError( $code ) {

    // Make sure the error code is an integer.
    $code = is_int($code) ? $code : (int) trim($code, '/');

    // Get the error data from the cache if available.
    $error = static::$cache->get("errors/$code");

    // Get the error's index data path.
    $index = static::$index."/endpoints/$code.php";

    // Get the last cached time of the index data.
    $cached = isset($error) ? File::modified(static::$cache->resolve("errors/$code")) : -1;
    $indexed = Cache::exists($index) ? Cache::modified($index) : -1;

    // If no error data was found, or if the error is outdated, then cache it now.
    if( !isset($error) or $cached < $indexed ) {

      // If some index data exists for the endpoint, then use it.
      if( $indexed > -1 ) {

        // Get the $error's index data.
        $error = Cache::include($index);

        // Save the error's index data to the cache.
        static::$cache->set("errors/$code", $error);

      }

      // Otherwise, for errors that are not 404, return a 404 error page instead.
      else if( $code !== 404 ) return static::getError(404);

      // Otherwise, throw a generic 404 failure.
      else return new Failure(404);

    }

    // Get the error data.
    $error = $error['data'];

    // Return the error data.
    return $error;

  }

  // Derive partial data from cached index data.
  protected static function getPartials() {

    // Get the partials data from the cache if available.
    $partials = static::$cache->get("partials");

    // Get the partials' index data path.
    $index = static::$index."/partials.php";

    // Get the last cached time of the index data.
    $cached = isset($partials) ? File::modified(static::$cache->resolve("partials")) : -1;
    $indexed = Cache::exists($index) ? Cache::modified($index) : -1;

    // If no partials data was found, or if the partials are outdated, then cache it now.
    if( !isset($partials) or $cached < $indexed ) {

      // If some index data exists for the endpoint, then use it.
      if( $indexed > -1 ) {

        // Get the partials' index data.
        $partials = Cache::include($index);

        // Save the partials' index data to the cache.
        static::$cache->set("partials", $partials);

      }

      // Otherwise, return a 404 error page instead.
      else return static::getError(404);

    }

    // Get the partials data.
    $partials = $partials['data'];

    // Return the partials data.
    return $partials;

  }

  // Derive helper data from cached index data.
  protected static function getHelpers() {

    // Get the helpers data from the cache if available.
    $helpers = static::$cache->get("helpers");

    // Get the helpers' index data path.
    $index = static::$index."/helpers.php";

    // Get the last cached time of the index data.
    $cached = isset($helpers) ? File::modified(static::$cache->resolve("helpers")) : -1;
    $indexed = Cache::exists($index) ? Cache::modified($index) : -1;

    // If no helpers data was found, or if the helpers are outdated, then cache it now.
    if( !isset($helpers) or $cached < $indexed ) {

      // If some index data exists for the endpoint, then use it.
      if( $indexed > -1 ) {

        // Get the helpers' index data.
        $helpers = Cache::include($index);

        // Save the helpers' index data to the cache.
        static::$cache->set("helpers", $helpers);

      }

      // Otherwise, return a 404 error page instead.
      else return static::getError(404);

    }

    // Get the helpers data.
    $helpers = $helpers['data'];

    // Return the helpers data.
    return $helpers;

  }

}

?>
