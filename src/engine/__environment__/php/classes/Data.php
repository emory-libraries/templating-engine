<?php

/*
 * Data
 *
 * Reads and parses a data file given a file path.
 */
class Data {
  
  // The path of the data file.
  public $path;
  
  // The contents of the data file.
  public $data = [];
  
  // Constructs the data.
  function __construct( $path ) {
    
    // Extract path data when given a string.
    if( is_string($path) ) {
    
      // Save the data file path.
      $this->path = $path;

      // Read the data file at the given path.
      $data = File::read($path);

      // Get the data file's extension.
      $ext = pathinfo($path, PATHINFO_EXTENSION); 

      // Transform the data based on the file extension.
      $this->data = Transformer::transform($data, $ext);
      
      // Convert any empty data to an array.
      if( !isset($this->data) or $this->data == '' ) $this->data = [];
      
    }
    
    // Otherwise, capture path data from an array.
    else if( is_array($path) ) {
      
      // Capture the data by mapping the the array keys to properties when applicable.
      if( isset($path['data']) ) {
      
        // Get data from the array.
        $this->data = $path['data'];
        $this->path = array_get($path, 'path');
        
      }
      
      // Otherwise, assume an array of data was given, and save it.
      else $this->data = $path;
      
    }
    
  }
  
  // Defines set state method for restoring state.
  public static function __set_state( array $state ) {
    
    // Initialize an instance of the class.
    $instance = new self(null);
    
    // Assign properties to the instance.
    foreach( $state as $property => $value ) { $instance->$property = $value; }
    
    // Return the instance.
    return $instance;
    
  }
  
  
}

?>