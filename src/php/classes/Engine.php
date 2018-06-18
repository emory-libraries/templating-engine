<?php

// Use dependencies.
use Symfony\Component\Yaml\Yaml;

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
    
    // Get file extensions.
    $ext = $this->config->EXT;
    
    // Get the route data.
    $route = trim($this->route->route, '/');
    $query = $this->route->query;
    
    // Get the template.
    $template = $route.$ext['template'];
  
    // Get the data.
    $data = file_get_contents($this->config->DATA."/".$route.$ext['data']);

    // Parse the data as JSON.
    if( in_array($ext['data'], ['.json']) ) $data = json_decode($data, true);
    
    // Or, parse the data as YAML.
    else if( in_array($ext['data'], ['.yml', '.yaml']) ) $data = Yaml::parse($data);
    
    // Merge any query data.
    $data = array_merge($data, $query);
    
    // Render the template.
    echo $this->parser->render($template, $data);
    
  }
  
}

?>