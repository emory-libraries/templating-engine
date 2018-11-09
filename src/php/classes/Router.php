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
  
  // Filter an array based on a exact key-value match.
  private function __filterRoutesExact( $key, $value ) {
    
    // Return the exact match.
    return array_filter_key($key, $value, $this->routes);
    
  }
  
  // Filter an array based on a close key-value match.
  private function __filterRoutesClose( $key, $value ) {
    
    // Return the close match.
    return array_values(array_filter($this->routes, function($route) use ($key, $value) {
      
      // Determine if the route is dynamic.
      $dynamic = isset($route['dynamic']) ? $route['dynamic'] : false;
     
      // Find the route path that almost matches the given path.
      return ($dynamic and strpos($value, $route[$key]) === 0);
      
    }));
    
  }
  
}

// Initalize the router's `GET` methods.
trait Router_GET {
  
  // Get all routes.
  public function getRoutes() { return $this->routes; }
  
  // Get a single route by path.
  public function getRouteByPath( $path ) {
    
    // Filter routes for the exact path.
    $result = $this->__filterRoutesExact('path', $path);
    
    // Otherwise, filter routes for a dynamic path.
    if( empty($result) ) {
      
      // Find any route data.
      $result = $this->__filterRoutesClose('path', $path);
      
      // Extract endpoint data from the path if applicable.
      if( !empty($result) ) { 
        
        // Extract endpoint data for the path(s).
        foreach( $result as &$route ) {
          
          // Identify the dynamic portion of the path.
          $route['endpoint'] = str_replace($route['path'], '', $path);
          
        }
        
      }
      
    }
  
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