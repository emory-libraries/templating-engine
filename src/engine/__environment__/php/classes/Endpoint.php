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
  
  // The data file that corresponds to the given endpoint, if applicable.
  public $file;
  
  // The data file that the endpoint uses.
  public $data;
  
  // The template file that the endpoint uses.
  public $template;
  
  // Capture the template file ID.
  public $tid;
  
  // Indicates whether the endpoint is for an asset.
  public $asset = false;
  
  // Identifies the an asset endpoint's mime type, if applicable.
  public $mime;
  
  // Constructs the endpoint.
  function __construct( Route $route, Index $index ) { 
    
    // Determine if the route is for an asset.
    $this->asset = $route->template === 1;

    // Capture data about the endpoint(s).
    $this->id = $route->id;
    $this->eid = $this->asset ? $route->endpoint : (isset($route->path) ? File::endpoint($route->path, [
      CONFIG['data']['site']['root'],
      CONFIG['data']['environment']['root'],
      CONFIG['patterns']['root'],
      CONFIG['engine']['meta'],
      CONFIG['site'],
      CONFIG['engine'],
      CONFIG['data']['site']['root']
    ]) : $route->endpoint);
    $this->file = $route->path;
    $this->endpoint = $route->endpoint;
    $this->redirect = $route->redirect;
    $this->url = is_array($route->endpoint) ? array_map(function($endpoint) use ($route) {
      
      // For routes with multiple endpoints, map each endpoint to a URL.
      return $route->domain.$endpoint;
      
    }, $route->endpoint) : $route->domain.$route->endpoint;

    // Get the template that the endpoint uses.
    $this->template = $index->getEndpointTemplate($this->eid);
    $this->tid = $route->template;
    
    // Capture the asset's mime type, if applicable.
    if( $this->asset ) $this->mime = Mime::type(pathinfo($this->id, PATHINFO_EXTENSION));
    
    // Get all data that the endpoint uses.
    $this->data = array_merge([
      '__meta__' => $index->getMetaData(),
      '__global__' => $index->getGlobalData(),
      '__shared__' => $index->getSharedData(),
      '__route__' => $route
    ], $index->getEndpointData($this->eid));
    
    // Save the finalized endpoint data.
    $this->data['__endpoint__'] = object_to_array($this);
    
  }
  
}

?>