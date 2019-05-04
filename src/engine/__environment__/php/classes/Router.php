<?php

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
    $this->endpoint = API::get('/endpoint/'.$request->endpoint);

  }

  // Redirects to a different page using either an internal URI or an external URL.
  function redirect( $path, $permanent = false ) {

    // Convert relative paths to absolute paths.
    if( preg_match('/^(?!(http(s)?:)?\/\/).+$/i', $path) ) {

      // Get the site's base path.
      $site = cleanpath('/'.str_replace(CONFIG['document']['root'], '', CONFIG['site']['root']));

      // Get the site's absolute path to the destination.
      $path = absolute_path_from_root($site."/$path");

    }

    // Detect URLs and redirect.
    header("Location: $path", true, ($permanent ? 301 : 302));

  }

  // Renders an endpoint.
  // If the endpoint redirects, then it will redirect the given location.
  // If the endpoint doesn't exist, then it will render an error page instead.
  function render() {

    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Router', true);
    
    // If the endpoint redirects, then redirect.
    if( $this->endpoint->redirect !== false ) return $this->redirect($this->endpoint->redirect);

    // Otherwise, if the endpoint causes an error, then get the error page.
    if( $this->endpoint->error !== false ) return Renderer::error($this->endpoint);
    
    // Otherwise, if the endpoint is an asset, then get the asset.
    if( $this->endpoint->asset !== false ) return Renderer::asset($this->endpoint);
    
    // Otherwise, render the endpoint.
    return Renderer::render($this->endpoint);

  }

}

?>
