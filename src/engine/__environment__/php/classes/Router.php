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

  // Some route data about the requested endpoint.
  public $route;

  // Constructs the router.
  function __construct( Request $request ) {

    // Capture data about the request.
    $this->request = $request;

    // Derive route data from the request.
    $this->route = new Route($request);

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

    // Immediately detect invalid endpoints, and point them at a 404 error page.
    if( !File::isFile($this->route->data) ) return Renderer::error( 404 );

    // Get the endpoint's data file.
    $file = is_array($this->route->data) ? array_values(array_filter($this->route->data, 'File::exists'))[0] : $this->route->data;

    // Detect assets endpoints, and allow them to fall through the rendering process.
    if( $this->route->asset ) return Renderer::asset($file);

    // Get the endpoint's data.
    $data = new Data($file);

    // Detect redirects as soon as possible, and redirect to that location.
    if( array_get($data->data, 'redirect') !== null ) return $this->redirect($data->data['redirect']);

    // Determine the endpoint's template PLID.
    $plid = array_keys(CONFIG['config']['template'])[array_search($data->data['template'], array_values(CONFIG['config']['template']))];

    // Get the endpoint's template.
    $template = new Template($this->route->templates[$plid]);

    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Router: Get Endpoint\'s Template');

    // Compile the data for the requested endpoint.
    $data = Data::compile($data, $this->request, $this->route);

    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Router: Compile Endpoint\'s Data');

    // Mutate the data for the endpoint.
    $data->data = Mutator::mutate($data->data, $plid);

    // Add benchmark point.
    if( DEVELOPMENT ) {
      Performance\Performance::point('Mutations applied to data.');
      Performance\Performance::finish('Router');
    }

    // Render the endpoint.
    return Renderer::render($this->route, $data, $template);

  }

}

?>
