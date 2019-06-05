<?php

namespace CustomHelpers;

trait ApiHelpers {

  // Use the API to get some data out of the index.
  public static function API( $method, $endpoint = null ) {
    
    // Get the API endppoint.
    $endpoint = func_num_args() > 2 ? $endpoint : $method;
    
    // Get the API method, or use a `GET` method by default.
    $method = func_num_args() > 2 ? strtolower($method) : 'get';
    
    // Pass along the request to the API and get the response.
    $response = API::$method($endpoint);
    
    // Convert any objects to arrays.
    $response = object_to_array($response);
    
    // Return the API response.
    return $response;
    
  }
  
}

?>