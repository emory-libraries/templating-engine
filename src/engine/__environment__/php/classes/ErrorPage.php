<?php

// Initialize utility methods.
trait ErrorPage_Utilities {
  
  // Attempt to load and parse errors from an error file.
  private function __loadErrors() {
    
    // Initialize the results.
    $result = [];
    
    // Get the error file's path.
    // NOTE: If error file is `.xml`, will need to create `XMLParser` class to use here and with `Data::__parseXML`
    $path = CONFIG['data']['site']['meta']."/errors.json";
    
    // Verify that the error file exists.
    if( file_exists($path) ) {
      
      // Read the error file. 
      $result = json_decode(file_get_contents($path), true);
      
    }
    
    // Return the results.
    return $result;
    
  }
  
}

// Creates an `ErrorPage` class for handling error pages.
class ErrorPage {
  
  // Load traits.
  use ErrorPage_Utilities;
  
  // Define errors
  protected $errors = [];
  
  // Capture the error code.
  private $code;
  
  // Constructor
  function __construct( int $code ) {
    
    // Load error data.
    $this->errors = $this->__loadErrors();
    
    // Capture the error code.
    $this->code = $code;
    
    // Trigger the appropriate HTTP response.
    http_response_code($this->code);
    
  }
  
  // Get the error data.
  public function getData() { 
    
    // Find the error by code.
    $error = array_values(array_filter($this->errors, function($error) {
      
      // Match the error codes.
      return $error['code'] === $this->code;
      
    }));
      
    // Return the error data or nothing otherwise.
    return (!empty($error)) ? $error[0] : [];
      
  }
  
}

?>