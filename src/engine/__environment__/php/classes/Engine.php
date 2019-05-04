<?php

/*
 * Engine
 *
 * This is the core of the templating engine. It's responsible for processing
 * the request, deciphering the correct data file(s) and pattern(s) to use, and
 * finally rendering the page.
 */
class Engine {
  
  // A single cache for the site.
  protected $cache;
  
  // The API interface for accessing data.
  protected $api;
  
  // Some data about the request.
  protected $request;
  
  // An index of all known data and templates.
  protected $index;
  
  // A router to handle page rendering, redirecting, and erroring.
  protected $router;
  
  // Constructor
  function __construct() {
    
    // Use the global cache.
    global $cache;
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Engine', true);
    
    // Initialize the cache.
    $this->cache = $cache;
    
    // Initialize the API.
    $this->api = new API($this->cache);
    
    // Get data about the request.
    $this->request = new Request();
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Request processed.');
    
    // Initialize the router.
    $this->router = new Router($this->request);
    
    // Run the templating engine.
    $this->run();
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::finish('Engine');
    
  }
  
  // Parse the route.
  private function run() {
    
    // Attempt to load the requested endpoint.
    echo $this->router->render();
    
  }
  
}

?>