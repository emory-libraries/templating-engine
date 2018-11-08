<?php

// Checks whether or not all values within an array meet some criteria.
function array_every( array $array, callable $callback ) {
  
  foreach( $array as $key => $value ) {
    
    if( $callback($value, $key) === false ) return false;
    
  }
  
  return true;
  
}

// Compares two arrays for equivalence.
function array_equiv( array $a, array $b, $deep = true ) {
  
  $result = [];
  
  foreach( $a as $key => $a_value ) {
    
    if( !array_key_exists($key, $b) ) return false;
    
    $b_value = $b[$key];
    $a_type = gettype($a_value);
    $b_type = gettype($b_value);
    
    if( $a_type == 'array' and $b_type != 'array' ) $result[$key] = false;
    
    else if( $b_type == 'array' and $a_type != 'array' ) $result[$key] = false;
    
    else if( $a_type == 'array' and $b_type == 'array' ) {
      
      if( $deep ) $result[$key] = array_equiv($a_value, $b_value, $deep);
      
      else $result[$key] = true;
      
    }
    
    else $result[$key] = $a_value == $b_value;
    
  }
 
  $result = array_every($result, function($value) {
    
    return $value === true;
    
  });
    
  return $result;
  
}

// Retrieve a value from an array or return its literal interpretation (`null`).
function array_get( array $array, $keys ) {
  
  // Permit dot-notation in key values.
  $keys = explode('.', $keys);
  
  // Initialize the result.
  $result = $array;
  
  // Narrow down the values.
  foreach( $keys as $key ) {
    
    // Narrow down multi-dimensional arrays.
    if( is_array($result) and array_key_exists($key, $result) ) $result = $result[$key];
      
    // Otherwise, no value exists.
    else return null;
    
  }
  
  // Return the result.
  return $result;
  
}

// Flatten an array.
function array_flatten( array $expanded, $delimiter = '.', $parent = null ) {
  
  // Initialize the result.
  $result = [];
  
  // Recursively flatten the array.
  foreach( $expanded as $key => $value ) {
    
    // Handle nested arrays.
    if( is_array($value) ) {
      
      // Handle children with parents.
      if( isset($parent) ) {
        
        // Flatten the child array.
        $result = array_merge($result, array_flatten($value, $delimiter, $parent.$delimiter.$key));
        
      }
      
      // Otherwise, handle children without parents.
      else {
        
        // Flatten the child array.
        $result = array_merge($result, array_flatten($value, $delimiter, $key));
        
      }
      
    }
    
    // Otherwise, handle simple values.
    else {
      
      // Handle keys with parents.
      if( isset($parent) ) $result[$parent.$delimiter.$key] = $value;
      
      // Otherwise, handle keys.
      else $result[$key] = $value;
      
    }
    
  }
  
  // Return the result.
  return $result;
  
}

// Expand a previously flattened array.
function array_expand( array $flattened, $delimiter = '.' ) {
  
  // Initialize the result.
  $result = [];
  
  // Recursively expand the array.
  foreach( $flattened as $key => $value ) { 
    
    // Expand the key.
    $keys = explode($delimiter, $key);
    
    // Get a pointer to the result.
    $pointer = &$result;
    
    // Build the array from the keys.
    foreach( $keys as $index => $key ) {
      
      // Rebuild the array structure.
      if( $index < count($keys) - 1 ) {
        
        // Create the nested array if it doesn't exist.
        if( !array_key_exists($key, $pointer) ) $pointer[$key] = [];
        
        // Move the pointer.
        $pointer = &$pointer[$key];
        
      }
      
      // Otherwise, assign the value.
      else $pointer[$key] = $value;
      
    }
    
  }
  
  // Return the result.
  return $result;
  
}

?>