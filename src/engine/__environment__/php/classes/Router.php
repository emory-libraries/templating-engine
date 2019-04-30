<?php

/*
 * Router Utilities
 *
 * Utility methods for the Router class.
 */
trait Router_Utilities {
  
  // Map routes to router endpoints.
  private static function mapRoutesToEndpoints( array $routes, Index $index ) {
    
    // Initialize endpoints.
    $endpoints = [];
    
    // Traverse the routes.
    foreach( $routes as $id => $route ) {
      
      // Recursively map routes within nested arrays to their endpoints.
      if( is_array($route) ) $endpoints = array_merge($endpoints, self::mapRoutesToEndpoints($route, $index));
      
      // Otherwise, map the current route to its endpoint.
      else $endpoints[] = new Endpoint($route, $index);
      
    }
    
    // Return endpoints.
    return $endpoints;
    
  }
  
}

/*
 * Router
 * 
 * Interprets route data from an index and creates an endpoint API.
 * The router is what's responsible for rendering valid endpoints,
 * redirecting for endpoints with redirects, and/or forcing an error
 * page for all invalid and unknown routes. 
 */
class Router {
  
  // Load utility methods.
  use Router_Utilities;
  
  // The list of known routes found in the index.
  protected $routes = [];
  
  // The set of recognized endpoints within the site.
  protected $endpoints = [];
  
  // Constructs the router.
  function __construct( Index $index ) {
    
    // Save the routes in the index.
    $this->routes = $index->routes;

    // Map routes to endpoints.
    $this->endpoints = self::mapRoutesToEndpoints($index->routes, $index);
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Routes converted to endpoints.');
 
  }
  
  // Determines if an endpoint exists.
  function endpointExists( $endpoint ) {
    
    // Lookup the endpoint in the collection of known endpoints.
    $endpoint = array_values(array_filter($this->endpoints, function($data) use ($endpoint) {
      
      // Find the endpoint object with the given endpoint.
      return $data->endpoint == $endpoint;
      
    }));
    
    // Return whether or not the endpoint exists.
    return isset($endpoint[0]);
    
  }
  
  // Determines if an endpoint redirects.
  function endpointRedirects( $endpoint ) {
    
    // Lookup the endpoint in the collection of known endpoints.
    $endpoint = array_values(array_filter($this->endpoints, function($data) use ($endpoint) {
      
      // Find the endpoint object with the given endpoint.
      return $data->endpoint == $endpoint;
      
    }));
    
    // Return whether or not the endpoint exists.
    return isset($endpoint[0]);
    
  }
  
  // Gets the endpoint object for a given endpoint.
  function getEndpoint( $endpoint ) {
    
    // Lookup the endpoint in the collection of known endpoints.
    $endpoint = array_values(array_filter($this->endpoints, function($data) use ($endpoint) {
      
      // Find the endpoint object with the given endpoint.
      return (is_array($data->endpoint) ? in_array($endpoint, $data->endpoint) : $data->endpoint == $endpoint);
      
    }));
    
    // Return the endpoint if it exists, or use a 404 error endpoint otherwise.
    return (isset($endpoint[0]) ? $endpoint[0] : $this->getEndpoint('/404'));
    
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
  function render( $endpoint ) {
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Router', true);
    
    // Get the endpoint object for the given endpoint.
    $endpoint = $this->getEndpoint($endpoint);
   
    // If the endpoint redirects, redirect to the new location.
    if( $endpoint->redirect !== false ) return $this->redirect($endpoint->redirect);
    
    // Mutate the data for the endpoint.
    $endpoint->data = Mutator::mutate($endpoint->data, $endpoint->tid);
    
    // Add benchmark point.
    if( DEVELOPMENT ) {
      Performance\Performance::point('Mutations applied to data.');  
      Performance\Performance::finish('Router');
    }
    
    // Render the endpoint.
    return Renderer::render($endpoint);
    
  }
  
}

?>