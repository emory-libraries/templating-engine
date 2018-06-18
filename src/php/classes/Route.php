<?php

// Creates a `Route` class for managing endpoints.
class Route {
  
  // Capture configurations.
  protected $config;
  
  // Parse the endpoint.
  public $route = '';
  public $query = [];
  
  // Constructor
  function __construct( Config $config ) {
    
    // Save the configurations
    $this->config = $config;
    
    // Parse the endpoint.
    $endpoint = explode('?', str_replace($config->ROOT_PATH, '', $_SERVER['REQUEST_URI']), 2);
    
    // Save the endpoint data.
    $this->route = $endpoint[0];

    // Get the query string parameters, and cast their data types.
    $query = new Cast($_GET);
    
    // Save the query string.
    $this->query = $query->castAll();
    
  }
  
}

?>