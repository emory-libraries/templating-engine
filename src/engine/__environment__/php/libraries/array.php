<?php

// Get a value from an array using dot notation or return the default value.
function array_get( array $array, $keys, $default = null ) {
  
  // Set delimiter.
  $delimiter = '.';
  
  // Permit dot notation in key values.
  $keys = explode($delimiter, $keys);
  
  // Initialize the result.
  $result = $array;
  
  // Narrow down the values.
  foreach( $keys as $key ) {
    
    // Narrow down multi-dimensional arrays.
    if( is_array($result) and array_key_exists($key, $result) ) $result = $result[$key];
      
    // Otherwise, no value exists.
    else return $default;
    
  }
  
  // Return the result.
  return $result;
  
}

// Set a value within an array using dot notation.
function array_set( array $array, $keys, $value, $override = false ) {

  // Set delimiter.
  $delimiter = '.';
  
  // Permit dot notation in keys values.
  $keys = explode($delimiter, $keys);
  
  // Initialize the result.
  $result = $array;
  
  // Intialize a pointer for traversing the array.
  $pointer = &$result;
  
  // Get the key index and length.
  $index = 0;
  $length = count($keys);
  
  // Determine if the value can be set.
  $set = false;
  
  // Find the target location.
  foreach( $keys as $key ) {
    
    // Create the key if it doesn't exit.
    if( !array_key_exists($key, $pointer) ) $pointer[$key] = [];
    
    // Otherwise, determine if the value should be overriden.
    else if( !is_array($pointer[$key]) ) {
      
      // Prevent overriding of non-array values unless override flag is given.
      if( $override ) $pointer[$key] = [];
        
      // Otherwise, the value should not be overriden, so stop trying.
      else break;
        
    }
    
    // Move the pointer to the new location.
    $pointer = &$pointer[$key];
    
    // Increment the index.
    $index++;
    
    // Change the set flag if all keys were found without issues.
    if( $index === $length ) $set = true;
    
  }
  
  // Set the new value.
  if( $set ) $pointer = $value;
  
  // Return the array with the newly set value, or the unchanged array otherwise.
  return $result;

  
}

// Unset a value within an array using dot notation.
function array_unset( array $array, $keys, $index = 0 ) {
  
  // Set delimiter.
  $delimiter = '.';
  
  // Permit dot notation in keys values.
  $keys = explode($delimiter, $keys);
  
  // Initialize the result.
  $result = [];
  
  // Recursively duplicate the array but leave of the key.
  foreach( $array as $key => $value ) {
    
    // See if the keys match, and ignore it only if its the target endpoint within the array.
    if( $key == $keys[$index] ) {

      // Ignore the value if it's the target endpoint within the array.
      if( $index + 1 == count($keys) ) continue;
      
      // Otherwise, recursively look inside arrays.
      else if( is_array($value) ) $result[$key] = array_unset($value, $keys, $index + 1);
      
    }
    
    // Otherwise, capture the value per usual.
    else $result[$key] = $value;
    
  }
  
  // Return the array without the value, or the unchanged array otherwise.
  return $result;
  
}

// Flattens an array.
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

// Expands a previously flattened array.
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

// Determine if an array is an associative array.
function is_associative_array( array $array ) {
  
  // Check empty array.
  if( [] === $array ) return false;
  
  // Otherwise, check arrays with items.
  return array_keys($array) !== range(0, count($array) - 1);
  
}

// Map array keys instead of values.
function array_map_keys( callable $callback, array &$array ) {
  
  // Loop through the array, and call the callback with the key.
  foreach( $array as $key => $value ) {
    
    // Pass the key to the callback and capture the result.
    $result = call_user_func($callback, $key);
    
    // Remove the old array key.
    unset($array[$key]);
    
    // Use the new array key instead.
    $array[$result] = $value;
    
  }
  
  // Return the array.
  return $array;
  
}

// Quickly filter an associate array by key based on a given value.
function array_filter_key( $key, $value, array $array ) {
  
  // Filter the array by key and value.
  return array_values(array_filter($array, function($item) use ($key, $value) {
      
    // Find the route path that matches the given path.
    return $item[$key] == $value;

  }));
  
}

// Merge two or more arrays while maintaining exact key values.
function array_merge_exact( array $array, array ...$arrays ) {
  
  // Initialize the result.
  $result = $array;
  
  // Merge the other arrays into the result.
  foreach( $arrays as $array ) {
    
    // Merge each array maintaining its same key.
    foreach( $array as $key => $value ) {
      
      // Merge the array item into the result.
      $results[$key] = $value;
      
    }
    
  }
  
  // Return the result.
  return $result;
  
}

// Merge two or more arrays recursively while maintaining exact key values.
function array_merge_exact_recursive( array $array, array ...$arrays ) {
  
  // Initialize the result.
  $result = $array;
  
  // Merge the other arrays into the result.
  foreach( $arrays as $array ) {
    
    // Merge each array maintaining its same key.
    foreach( $array as $key => $value ) {
      
      // Merge into existing keys.
      if( array_key_exists($key, $result) ) {
        
        // Recursively merge arrays.
        if( is_array($result[$key]) ) {
          
          // Merge the array recursively.
          $result[$key] = array_merge_exact_recursive($result[$key], $value);
          
        }
        
        // Otherwise, convert the existing key to an array.
        else {
          
          // Convert the result to an array.
          $result[$key] = [$result[$key]];

          // Add to the new array item.
          $result[$key][] = $value;
          
        }
        
      }
      
      // Otherwise, create the new key.
      else $result[$key] = $value;
      
    }
    
  }
  
  // Return the result.
  return $result;
  
}

?>