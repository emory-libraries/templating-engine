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
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::point('Engine', true);
    
    // Get data about the request.
    $this->request = new Request();
    
    // Add benchmark point.
    if( DEVELOPMENT ) {
      Performance\Performance::point('Request processed.');
      Performance\Performance::point('Indexing', true);
    }
    
    // Index all data and templates.
    $this->index = new Index();
    
    // Save index data globally.
    define('INDEX', object_to_array($this->index));
    define('SITE_DATA_INDEX', object_to_array([
      'meta' => $this->index->getMetaData(),
      'global' => $this->index->getGlobalData(),
      'shared' => $this->index->getSharedData(),
      'site' => $this->index->data['site']['site']
    ]));
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::finish('Indexing');
    
    // Initialize the router.
    $this->router = new Router($this->index);
    
    // Run the templating engine.
    $this->run();
    
    // Add benchmark point.
    if( DEVELOPMENT ) Performance\Performance::finish('Engine');
    
  }
  
  // Parse the route.
  private function run() {
    
    // Attempt to load the requested endpoint.
    echo $this->router->render($this->request->endpoint);
    
  }
  
}

?>