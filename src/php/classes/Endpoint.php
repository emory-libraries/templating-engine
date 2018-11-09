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
  
}

// Creates an `Endpoint` class for building the active endpoint.
class Endpoint {
  
  // Load traits.
  use Endpoint_Utilities;
  
  // Initialize the router.
  protected $router;
  
  // Capture configurations.
  protected $config;
  
  // Parse the endpoint, data, and template.
  private $endpoint = null;
  private $route = [];
  private $data = [];
  private $template = [];
  
  // Determine if the endpoint is dynamic.
  private $dynamic = false;
  
  // Constructor
  function __construct() {
    
    // Use global configurations.
    global $config;
    
    // Save the configurations
    $this->config = $config;
    
    // Capture the endpoint.
    $this->endpoint = $this->__getEndpointClean();
    
    // Initialize the router.
    $this->router = new Router();
    
    // Find the endpoint's route, if one exists.
    $this->route = $this->router->getRouteByPath($this->endpoint);
    
    // Determine if the endpoint is dynamic.
    if( isset($this->route['dynamic']) and in_array($this->route['dynamic'], [true, false]) ) {
      
      // Capture the dynamic state.
      $this->dynamic = $this->route['dynamic'];
      
    }
    
    // Determine the template that should be used default.
    $template = isset($this->route) ? array_get($this->route, 'template') : 'error';
    
    // Define any data that should be merged.
    $data = $template == 'error' ? (new ErrorPage(404))->getData() : [];

    // Get the route's template.
    $this->template = new Template($template);

    // Get the route's data.
    $this->data = new Data($this->route, array_merge($data, [
      '__endpoint__' => $this->endpoint
    ]));
    
  }
  
  // Get the endpoint.
  public function getEndpoint() { return $this->endpoint; }
  
  // Get the endpoint's template.
  public function getTemplate() { 
    
    // Use the dynamic template if the endpoint is dynamic.
    if( $this->dynamic ) {
      
      // Get the route's dynamic data.
      $dynamic = $this->data->getDynamicData($this->route);
      
      // Verify that the dynamic data has a valid endpoint.
      if( isset($dynamic['endpoint']) ) {
        
        // Use the template for the dynamic endpoint.
        return $this->template->getTemplate($dynamic['endpoint']['template']); 
        
      }
      
    }
    
    // Otherwise, use the default template.
    return $this->template->getTemplate();
  
  }
  
  // Get the endpoint's data.
  public function getData( $merge = [] ) { return $this->data->getData(null, $merge); }
  
}

?>