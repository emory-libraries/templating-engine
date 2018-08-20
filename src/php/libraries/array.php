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

?>