<?php

/*
 * Endpoint
 *
 * Some data about an active endpoint within the site
 * based on a known route.
 */
class Endpoint {
  
  // The route that the endpoint inherits.
  public $id;
  
  // The true, un-aliased URI endpoint.
  public $eid;
  
  // One or more URI endpoints that the endpoint uses.
  public $endpoint;
  
  // A location that the endpoint redirects to, if applicable.
  public $redirect = false;
  
  // One or more complete URLs that the endpoint uses.
  public $url;
  
  // The data file that the endpoint uses.
  public $data;
  
  // The template file that the endpoint uses.
  public $template;
  
  // Constructs the endpoint.
  function __construct( Route $route, Index $index ) { 
    
    // Capture data about the endpoint(s).
    $this->id = $route->id;
    $this->eid = (isset($route->path) ? File::endpoint($route->path) : $route->endpoint);
    $this->endpoint = $route->endpoint;
    $this->redirect = $route->redirect;
    $this->url = is_array($route->endpoint) ? array_map(function($endpoint) use ($route) {
      
      // For routes with multiple endpoints, map each endpoint to a URL.
      return $route->domain.$endpoint;
      
    }, $route->endpoint) : $route->domain.$route->endpoint;

    // Get all data that the endpoint uses.
    $this->data = array_merge([
      '__meta__' => $index->getMetaData(),
      '__global__' => $index->getGlobalData(),
      '__shared__' => $index->getSharedData()
    ], $index->getEndpointData($this->eid));
    
    // Get the template that the endpoint uses.
    $this->template = $index->getEndpointTemplate($this->eid);

    // Mutate the data based on the template, if applicable.
    $this->data = Mutator::mutate($this->data, $route->template);
    
  }
  
}

?>