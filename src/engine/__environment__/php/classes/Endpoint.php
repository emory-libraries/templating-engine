<?php

/*
 * Endpoint
 *
 * Some data for an endpoint within the site based on a route.
 */
class Endpoint {
  
  // The route data for the endpoint.
  public $route;
  
  // The data file that the endpoint uses.
  public $data;
  
  // The pattern that the endpoint uses.
  public $pattern;
  
  // The template data the the endpoint uses.
  public $template = '';
  
  // Indicates if the endpoint redirects, and if so, where it redirects to.
  public $redirect = false;
  
  // Indicates if the endpoint is an asset.
  public $asset = false;
  
  // Indicates if the endpoint is for an error page, and if so, what the error code is.
  public $error = false;
  
  // Constructs the endpoint.
  function __construct( Route $route, array $data, Pattern $pattern ) { 
    
    // Capture the endpoint's route.
    $this->route = $route;
    
    // Capture the endpoint's data.
    $this->data = $data;
    
    // Capture the endpoint's pattern, and its template.
    $this->pattern = $pattern;
    $this->template = $pattern->pattern;
    
    // Determine if the endpoint redirects, and if so, capture its redirect location.
    if( isset($data['redirect']) ) $this->redirect = $data['redirect'];
    
    // Determine if the endpoint is an asset.
    if( $route->asset ) $this->asset = true;
    
    // Determine if the endpoint is for an error page.
    if( $route->error ) $this->error = (int) $route->id;
    
  }
  
  // Defines set state method for restoring state.
  public static function __set_state( array $state ) {
    
    // Initialize an instance of the class.
    $instance = new self(new Route(null), [], new Pattern(null));
    
    // Assign properties to the instance.
    foreach( $state as $property => $value ) { $instance->$property = $value; }
    
    // Return the instance.
    return $instance;
    
  }
  
}

?>