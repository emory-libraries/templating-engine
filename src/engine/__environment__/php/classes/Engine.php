<?php

// Builds the templating engine's `Engine` class.
class Engine {
  
  // Load the endpoint.
  protected $endpoint;
  
  // Load utilities.
  protected $parser;
  
  // Constructor
  function __construct() {
    
    // Get the current endpoint.
    $this->endpoint = new Endpoint(); 
    
    // Load utilities.
    $this->parser = new Parser();
    
    // Run the templating engine.
    $this->run();
    
  }
  
  // Parse the route.
  private function run() {
    
    // Get the template.
    $template = $this->endpoint->getTemplate();
    
    // Get the data.
    $data = $this->endpoint->getData([
      '__template__' => [
        'path' => ($path = $template['template']),
        'extension' => ($ext = pathinfo($path, PATHINFO_EXTENSION)),
        'filename' => basename($path),
        'basename' => basename($path, ".{$ext}"),
        'cache' => $template['cache']
      ]
    ]);
    
    // Render the template.
    echo $this->parser->render($template, $data);
    
  }
  
}

?>