<?php

// Builds the templating engine's `Engine` class.
class Engine {
  
  // Capture configurations.
  protected $config;
  
  // Load the endpoint.
  protected $endpoint;
  
  // Load utilities.
  protected $parser;
  
  // Constructor
  function __construct() {
    
    // Use global configurations.
    global $config;
    
    // Save the configurations.
    $this->config = $config;
    
    // Get the current endpoint.
    $this->endpoint = new Endpoint(); 
    
    // Load utilities.
    $this->parser = new Parser();
    
    // Run the templating engine.
    $this->run();
    
  }
  
  // Parse the route.
  private function run() {
    
    // Render the template.
    echo $this->parser->render($this->endpoint->getTemplate(), $this->endpoint->getData());
    
  }
  
}

?>