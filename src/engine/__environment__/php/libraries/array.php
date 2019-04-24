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

// Get the first item of an array.
function array_first( array $array ) {
  
  return (isset($array) ? $array[0] : null);
  
}

// Get the last item of an array.
function array_last( array $array ) {
  
  return (isset($array) ? $array[count($array) - 1] : null);
  
}

// Get the head (all but the last item) of the array.
function array_head( array $array ) {
  
  return array_slice($array, 0, -1);
  
}

// Get the tail (all but the first item) of the array.
function array_tail( array $array ) {
  
  return array_slice($array, 1);
  
}

// Filter an array by a given value.
function array_filter_by( array $array, $value = null ) {
  
  // Assume string value should filter by a key.
  if( is_string($value) ) {
    
    return array_values(array_filter($array, function($v, $k) use ($value) {
      
      return (array_key_exists($value, $v) and $v[$value]);
      
    }, ARRAY_FILTER_USE_BOTH));
    
  }
  
  // Assume non-associative array values should filter by a key and value.
  if( is_array($value) and !is_associative_array($value) ) { 
    
    return array_values(array_filter($array, function($v, $k) use ($value) {
      
      return (array_key_exists($value[0], $v) and $v[$value[0]] == $value[1]);
      
    }, ARRAY_FILTER_USE_BOTH));
    
  }
  
  // Assume associative array values should filter by all key-value pairs.
  if( is_array($value) and is_associative_array($value) ) {
    
    $result = $array;
    
    foreach( $value as $key => $val ) {
      
      $result = array_values(array_filter($array, function($v, $k) use ($key, $val) {
      
        return (array_key_exists($key, $v) and $v[$key] == $val);

      }, ARRAY_FILTER_USE_BOTH));
      
    }
    
    return $result;
    
  }
  
  // Assume callable values should filter by function.
  if( is_callable($value) ) {
    
    return array_values(array_filter($array, $value, ARRAY_FILTER_USE_BOTH));
    
  }
  
  // Otherwise, filter but ignore value.
  return array_values(array_filter($array));
  
}

// Determines if a condition is met for some items within an array.
function array_some( array $array, callable $callback ) {
  
  foreach( $array as $key => $value ) {
    
    if( $callback($value, $key, $array) ) return true;
    
  }
  
  return false;
  
}

// Determines if a condition is met for all items within an array.
function array_every( array $array, callable $callback ) {
  
  foreach( $array as $key => $value ) {
    
    if( !$callback($value, $key, $array) ) return false;
    
  }
  
  return true;
  
}

// Sort an array by one or more values.
function array_sort_by( array $array, $value = null ) {
  
  // Assume that non-associative array values should sort on each key in ascending order.
  if( is_array($value) and !is_associative_array($value) ) {
    
    $args = array_reduce($value, function($args, $key) {
      
      return array_merge($args, [$key, SORT_ASC]);
      
    }, []);
    
    return call_user_func_array('array_multisort', $args);
    
  }
  
  // Assume that associative array values should short on each key based on a given order.
  if( is_array($value) and is_associative_array($value) ) {
    
    $args = array_reduce(array_keys($value), function($args, $key) use ($value) {
      
      return array_merge($args, [$key, $value[$key]]);
      
    }, []);
    
    return call_user_func_array('array_multisort', $args);
    
  }
  
  // Otherwise, perform a simple sort on the array.
  return sort($array);
  
}

// Map an array by a given value.
function array_map_by( array $array, $value ) {
  
  // Assume string values should map on the given key.
  if( is_string($value) ) {
    
    return array_map(function($item) use ($value) {
      
      return (array_key_exists($value, $item) ? $item[$value] : null);
      
    }, $value);
    
  }
  
  // Assume callable values should map normally.
  if( is_callable($value) ) return array_map($value, $array);
  
  // Otherwise, return the unmapped array.
  return $array;
  
}

// Get all combinations of values from the given arrays.
function array_combos( ...$arrays ) {
  
  // Only work with arrays.
  $arrays = array_values(array_filter($arrays, 'is_array'));
  
  // Ignore empty arrays.
  if( count($arrays) === 0 ) return [];
  
  // Ignore single arrays.
  if( count($arrays) == 1 ) return $arrays[0];
  
  // Initialize the result.
  $result = [];
  
  // Initialize a helper for combining arrays.
  $combine = function( array $result, array $array ) {
    
    // Initialize an index.
    $i = 0;
    
    // Capture any existing combinations before manipulating the result.
    $combos = $result;
    
    // Initialize a flag to determine when the result set needs reducing.
    $reduce = false;
    
    // Loop through all items in the array.
    foreach( $array as $item ) {
      
      // Determine if this is the first item to be added into the combinations.
      if( !isset($result[$i]) ) {
        
        // Initialize the combination by adding the item.
        $result[$i] = array_merge([], [$item]);
        
      }
      
      // Otherwise, tack the item onto a previous combination.
      else {
          
        // Map out the new set of item combinations.
        $map = array_map(function($combo) use ($item) {
          
          // Merge the item into the combination.
          return array_merge($combo, [$item]);

        }, $combos);

        // Save the new combinations with the item included.
        $result[$i] = array_merge($result[$i], $map);
        
        // Remove string values from the array.
        $result[$i] = array_values(array_filter($result[$i], 'is_array'));
        
        // Set the reduce flag.
        $reduce = true;
        
      }
      
      // Increment the index.
      $i++;
      
    }
    
    // Reduce the array to a single level if needed.
    if( $reduce ) {
      
      // Flatten nested arrays to a single level.
      $result = array_reduce($result, function($result, $combo) {

        // Flatten the array.
        return array_merge($result, $combo);

      }, []);
      
    }
    
    // Return the result.
    return $result;
    
  };
  
  // Initialize an index.
  $i = -1;
  
  // Recursively combine the arrays.
  while( ++$i < count($arrays) ) {
    
    // Merge all array combinations.
    $result = $combine($result, $arrays[$i]);
    
  }
  
  // Return the result.
  return array_values(array_filter($result, 'is_array'));
  
}

?>