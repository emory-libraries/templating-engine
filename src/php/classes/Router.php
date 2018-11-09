<?php

// Initialize utility methods.
trait Router_Utilities {
  
  // Attempt to load and parse routes from a router file.
  private function __loadRoutes() {
    
    // Initialize the results.
    $result = [];
    
    // Get the router file's path.
    // NOTE: If router file is `.xml`, will need to create `XMLParser` class to use here and with `Data::__parseXML`
    $path = "{$this->config->DATA_META}/router.json";
    
    // Verify that the router file exists.
    if( file_exists($path) ) {
      
      // Read the router file. 
      $result = json_decode(file_get_contents($path), true);
      
    }
    
    // Return the results.
    return $result;
    
  }
  
}

// Initalize the router's `GET` methods.
trait Router_GET {
  
  // Get all routes.
  public function getRoutes() { return $this->routes; }
  
  // Get a single route by path.
  public function getRouteByPath( $path ) {
    
    // Filter routes for a matching path.
    $result = array_values(array_filter($this->routes, function($route) use ($path) {
      
      // Find the route path that matches the given path.
      return $route['path'] == $path;
      
    }));
      
    // Return the route or nothing otherwise.
    return (!empty($result) ? $result[0] : null);
    
  }
  
}

// Creates a `Router` class for handling endpoints.
class Router {
  
  // Load traits.
  use Router_Utilities, Router_GET;
  
  // Capture configurations.
  protected $config;
  
  // Capture route data.
  private $routes = [];
  
  // Constructor
  function __construct() {
    
    // Use global configurations.
    global $config;
    
    // Save the configurations
    $this->config = $config;
    
    // Attempt to load route data.
    $this->routes = $this->__loadRoutes();
    
  }
  
}

?>