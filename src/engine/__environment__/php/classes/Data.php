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
    
    // Save the data file path.
    $this->path = $path;
    
    // Read the data file at the given path.
    $data = file_get_contents($path);
    
    // Get the data file's extension.
    $ext = pathinfo($path, PATHINFO_EXTENSION); 

    // Transform the data based on the file extension.
    $this->data = Transformer::transform($data, $ext);
    
  }
  
}

?>