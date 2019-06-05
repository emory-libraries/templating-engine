<?php

namespace CustomHelpers;

trait ApiHelpers {

  // Use the API to get some data out of the index.
  public static function API( $method, $endpoint = null ) {
    
    // Get the API endppoint.
    $endpoint = func_num_args() > 2 ? $endpoint : $method;
    
    // Get the API method, or use a `GET` method by default.
    $method = func_num_args() > 2 ? strtolower($method) : 'get';
    
    // Pass along the request to the API.
    return API::$method($endpoint);
    
  }
  
}

?>