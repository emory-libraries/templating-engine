<?php

// Use dependencies.
use Symfony\Component\Yaml\Yaml;

// Initialize utility methods.
trait Endpoint_Utilities {
  
  // Find a route within the router by path.
  
  // Get the current endpoint.
  private function __getEndpoint() {
    
    return explode('?', str_replace($this->config->ROOT_PATH, '', $_SERVER['REQUEST_URI']), 2);
    
  }
  
  // Get the current endpoint without trailing slashes.
  private function __getEndpointClean() {
    
    return ($endpoint = $this->__getEndpoint())[0] == '/' ? $endpoint[0] : rtrim($endpoint[0], '/');
    
  }
  
  // Get the template ID for an endpoint.
  private function __getEndpointTemplate( $endpoint ) {
    
    // Search the router for the endpoint's route.
    $route = $this->router->getRouteByPath($endpoint);
      
    // Return the route's template or a 404 error otherwise.
    return (isset($route) ? $route['template'] : 404);
    
  }
  
}

// Creates an `Endpoint` class for extracting data about the active endpoint.
class Endpoint {
  
  // Load traits.
  use Endpoint_Utilities;
  
  // Initialize the router.
  protected $router;
  
  // Capture configurations.
  protected $config;
  
  // Parse the endpoint, data, and template.
  private $endpoint;
  private $data;
  private $template;
  
  // Constructor
  function __construct() {
    
    // Use global configurations.
    global $config;
    
    // Save the configurations
    $this->config = $config;
    
    // Initialize the router.
    $this->router = new Router();
    
    // Capture the endpoint.
    $this->endpoint = $this->__getEndpointClean(); 
  
    // Get the endpoint's data and template.
    $this->data = new Data($this->endpoint); 
    $this->template = new Template($this->__getEndpointTemplate($this->endpoint));
    
  }
  
  // Get the endpoint.
  public function getEndpoint() { return $this->endpoint; }
  
  // Get the endpoint's template.
  public function getTemplate() { return $this->template->getTemplate(); }
  
  // Get the endpoint's data.
  public function getData( $merge = [] ) { return $this->data->getData(null, $merge); }
  
}

?>