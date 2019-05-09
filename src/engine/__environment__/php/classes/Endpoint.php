<?php

/*
 * Endpoint
 *
 * Some data for an endpoint within the site based on a route.
 */
class Endpoint {
  
  // The endpoint's endpoint(s).
  public $endpoint;
  
  // The route data for the endpoint.
  public $route;
  
  // The data that the endpoint uses.
  public $data;
  
  // The pattern that the endpoint uses, or `null` if no pattern could be found for the endpoint.
  public $pattern;
  
  // The template the the endpoint uses, if applicable.
  public $template;
  
  // Indicates if the endpoint redirects, and if so, where it redirects to.
  public $redirect = false;
  
  // Indicates if the endpoint is an asset.
  public $asset = false;
  
  // Indicates if the endpoint is for an error page, and if so, what the error code is.
  public $error = false;
  
  // Constructs the endpoint.
  function __construct( Route $route, Data $data = null, Pattern $pattern = null ) { 
    
    // Capture the endpoint's route.
    $this->route = $route;
    
    // Capture the endpoint's endpoint(s).
    $this->endpoint = $route->endpoint;
    
    // Capture the endpoint's data.
    $this->data = $data;
    
    // Capture the endpoint's pattern;
    $this->pattern = $pattern;
    
    // Capture the endpoint's template, if applicable.
    if( isset($pattern) ) $this->template = $pattern->pattern;
    
    // Determine if the endpoint redirects, and if so, capture its redirect location.
    if( isset($route->redirect) or isset($data->data['redirect']) ) $this->redirect = $route->redirect ?? $data->data['redirect'];
    
    // Determine if the endpoint is an asset.
    if( $route->asset ) $this->asset = true;
    
    // Determine if the endpoint is for an error page.
    if( $route->error ) $this->error = (int) $route->id;
    
  }
  
  // Defines set state method for restoring state.
  public static function __set_state( array $state ) {
    
    // Initialize an instance of the class.
    $instance = new self(new Route(null), new Data(null), new Pattern(null));
    
    // Assign properties to the instance.
    foreach( $state as $property => $value ) { $instance->$property = $value; }
    
    // Return the instance.
    return $instance;
    
  }
  
}

?>