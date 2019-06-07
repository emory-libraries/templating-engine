<?php

// Use dependencies.
use Engine\API;
use Performance\Performance;

/*
 * Engine
 *
 * This is the core of the templating engine. It's responsible for processing
 * the request, deciphering the correct data file(s) and pattern(s) to use, and
 * finally rendering the page.
 */
class Engine {
  
  // The API interface for accessing data.
  protected $api;
  
  // Some data about the request.
  protected $request;
  
  // An index of all known data and templates.
  protected $index;
  
  // A router to handle page rendering, redirecting, and erroring.
  protected $router;
  
  // The process ID of the current engine instance.
  public static $pid = null;
  
  // Constructor
  function __construct() {
    
    // Add benchmark point.
    if( BENCHMARKING ) Performance::point('Engine', true);
    
    // Set the process ID.
    self::$pid = uniqid(DOMAIN.':', true);
    
    // Initialize the API.
    $this->api = new API();
    
    // Get data about the request.
    $this->request = new Request();
    
    // Add benchmark point.
    if( BENCHMARKING ) Performance::point('Request processed.');
    
    // Initialize the router.
    $this->router = new Router($this->request);
    
    // Run the templating engine.
    $this->run();
    
    // Add benchmark point.
    if( BENCHMARKING ) Performance::finish('Engine');
    
  }
  
  // Parse the route.
  private function run() {
    
    // Attempt to load the requested endpoint.
    echo $this->router->render();
    
  }
  
}

?>