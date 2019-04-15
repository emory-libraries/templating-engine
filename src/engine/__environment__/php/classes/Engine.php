<?php

/*
 * Engine
 *
 * This is the core of the templating engine. It's responsible for processing
 * the request, deciphering the correct data file(s) and pattern(s) to use, and
 * finally rendering the page.
 */
class Engine {
  
  // Load the endpoint.
  //protected $endpoint;
  
  // Load utilities.
  //protected $parser;
  
  // Some data about the request.
  protected $request;
  
  // An index of all known data and templates.
  protected $index;
  
  // A router to handle page rendering, redirecting, and erroring.
  protected $router;
  
  // Constructor
  function __construct() {
    
    // Get data about the request.
    $this->request = new Request(); 
    
    // Index all data and templates.
    $this->index = new Index();  
    
    // Initialize the router.
    $this->router = new Router($this->index);
    
    // Run the templating engine.
    $this->run();
    
  }
  
  // Parse the route.
  private function run() {
    
    // Attempt to load the requested endpoint.
    echo $this->router->render($this->request->endpoint);
    
  }
  
}

?>