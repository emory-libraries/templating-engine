<?php

// Builds the templating engine's `Engine` class.
class Engine {
  
  // Capture configurations.
  protected $config;
  
  // Load the route.
  protected $route;
  
  // Load utilities.
  protected $parser;
  
  // Constructor
  function __construct( Config $config ) {
    
    // Save the configurations.
    $this->config = $config;
    
    // Load the route.
    $this->route = new Route($config); 
    
    // Load utilities.
    $this->parser = new Parser($config);
    
    // Run the templating engine.
    $this->run();
    
  }
  
  // Parse the route.
  private function run() {
    
    // Render the template.
    echo $this->parser->render($this->route->getTemplate(), $this->route->getData());
    
  }
  
}

?>