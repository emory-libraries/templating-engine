<?php

// Initialize utility methods.
trait Transformer_Utilities {
  
  // Convert associative arrays to non-associative arrays.
  private function __toNonAssociativeArray( array $array, array $keys, $recursive = true ) {
    
    // Initialize the result.
    $result = [];
    
    // Recursively find each key within the array and convert its value to a non-associative array.
    foreach( $result as $key => $value ) {
      
      // Determine if the key matches.
      if( in_array($key, $keys) ) {
        
        // Confirm that the item's value is a non-associative array.
        if( is_array($value) and is_associative_array($value) ) {
          
          // Convert the array to a non-associative array.
          $result[$key] = [$value];
          
        }
        
      }
      
      // Otherwise, recursively convert any nested arrays.
      else if( is_array($value) and $recursive === true ) {
        
        // Convert any nested arrays into non-associative arrays.
        $result[$key] = $this->__toNonAssociativeArray($value, $keys, true);
        
      }
      
    }
    
    // Return the result.
    return $result;
    
  }
  
}

// Initialize transformation methods.
trait Transformer_Transforms {
  
  // Transform XML data.
  private function __transformXML( $options = [] ) {
    
    // Initialize the result.
    $result = [];

    // Extract any XML meta data.
    foreach( array_flatten($this->model['meta']) as $key => $path ) {
      
      // Find and save the meta data.
      $result[$key] = array_get($this->data, $path);
      
    }
    
    // Extract the XML core data.
    $data = array_get($this->data, $this->model['data']);
    
    // Apply any configurations to the data.
    if( isset($this->model['config']) ) {
      
      // See if some data should be formatted as non-associative arrays.
      if( isset($this->model['config']['arrays']) ) {
        
        // Convert select associative arrays to non-associative arrays.
        $data = $this->__toNonAssociativeArrays($data, $this->model['config']['arrays']);
          
      }
      
    }
    
    // Combine the core data and meta data.
    $result = array_merge($result, array_flatten($data));
    
    // Apply any options.
    if( !empty($options) ) {
      
      // Convert keys to a given syntax.
      if( isset($options['case']) ) $result = array_map_keys($options['case'], $result);
      
    }
    
    // Cast all values to their appropriate type.
    $result = new Cast($result);
    
    // Return the result.
    return array_expand($result->CastAll());
    
  }
  
}

// Creates a `Transformer` class for handling data transformations.
class Transformer {
  
  // Load traits.
  use Transformer_Utilities, Transformer_Transforms;
  
  // Capture configurations.
  protected $config;
  
  // Capture the raw data.
  private $data = [];
  
  // Capture the data model.
  private $model = [];
  
  // Capture the data type.
  private $type = null;
  
  // Constructor
  function __construct( array $data, array $model, string $type ) {
    
    // Use global configurations.
    global $config;
    
    // Save the configurations.
    $this->config = $config;
    
    // Save the raw data.
    $this->data = $data;
    
    // Save the data model.
    $this->model = $model;
    
    // Save the data type.
    $this->type = $type;
    
  }
  
  // Get the transformed data.
  public function getTransformation( $options ) { return $this->{"__transform{$this->type}"}($options); }
  
}

?>