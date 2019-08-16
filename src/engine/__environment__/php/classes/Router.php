<?php

// Use dependencies.
use Engine\API;
use Performance\Performance;

/*
 * Router
 *
 * Analyzes an incoming request to determine what to do with it.
 * The router will either render the page, redirect, or produce
 * a 404 error based on whether or not a data file could be found
 * for the given endpoint.
 */
class Router {

  // The requested endpoint.
  public $request;

  // The endpoint to be processed.
  public $endpoint;

  // Constructs the router.
  function __construct( Request $request ) {

    // Capture data about the request.
    $this->request = $request;

    // Get the request's endpoint data from the API.
    $this->endpoint = API::get('/endpoint/'.ltrim($request->endpoint, '/'));

    // Merge query parameters into the endpoint's data.
    $this->endpoint->data->data['__params__'] = $this->request->params;

    // Merge configuration data into the endpoint's data.
    $this->endpoint->data->data['__config__'] = CONFIG;

    // Merge the endpoint into the endpoint's data.
    $this->endpoint->data->data['__endpoint__'] = object_to_array($this->endpoint);

    // Add the current datetime into endpoint's data.
    $this->endpoint->data->data['__datetime__'] = date(DATE_ISO8601, time());

    // Set a global to indicate when an asset has been requested.
    define('ASSET', is_a($this->endpoint, 'Asset'));

  }

  // Redirects to a different page using either an internal URI or an external URL.
  public static function redirect( $path, $permanent = false ) {

    // Convert path to root relative path.
    $path = Path::toRootRelative($path);

    // Detect URLs and redirect.
    header("Location: $path", true, ($permanent ? 301 : 302));

  }

  // Renders an endpoint.
  // If the endpoint redirects, then it will redirect the given location.
  // If the endpoint doesn't exist, then it will render an error page instead.
  function render() {

    // Add benchmark point.
    if( BENCHMARKING ) Performance::point('Router', true);

    // If the endpoint is an asset, then get the asset.
    if( is_a($this->endpoint, 'Asset') !== false ) return Renderer::asset($this->endpoint);

    // Otherwise, if the endpoint redirects, then redirect.
    if( $this->endpoint->redirect !== false ) return self::redirect($this->endpoint->redirect);

    // Otherwise, if the endpoint forces an error, then get the error page.
    if( $this->endpoint->error !== false ) return Renderer::error($this->endpoint);

    // Add benchmark point.
    if( BENCHMARKING ) Performance::finish('Router');

    // Otherwise, render the endpoint.
    return Renderer::render($this->endpoint);

  }

}

?>
