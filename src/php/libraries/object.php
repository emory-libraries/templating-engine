<?php

// Retrieve a value from an object (converting any objects to an array) or return its literal interpretation (`null`).
// NOTE: Type hinting for `Object` will fail on PHP <7.2.
function object_get( Object $object, $keys, $delimiter = '.' ) {
  
  // Get the path to the target value.
  $path = explode($delimiter, $keys);
  
  // Intiailze a pointer to hel find the data.
  $pointer = &$object;
  
  // Narrow down the values.
  foreach( $path as $index => $level ) {
    
    // Handle objects.
    if( gettype($pointer) === "object" ) {
    
      // Verify that the next level within the object tree exists.
      if( property_exists($pointer, $level) ) $pointer = $pointer->{$level};
      
      // Otherwise, no value exists.
      else return null;
      
    }
    
    // Otherwise, handle arrays.
    else if( is_array($pointer) ) return array_get($pointer, implode($delimiter, array_slice($path, $index)), $delimiter);
    
    // Otherwise, the path doesn't exist.
    else return null;
    
  }
  
  // Initialize the result.
  $result; 
  
  // Convert any objects to an array.
  if( gettype($pointer) === "object" ) {
  
    // Convert the object to an array.
    $result = object_to_array($pointer); 

    // Extract the array value if there's only one item within the array.
    if( !is_associative_array($result) and count($result) === 1 ) $result = $result[0];

  }

  // Return the result.
  return $result;
  
}

// Cast an object to an array.
function object_to_array( Object $object ) { return json_decode(json_encode($object), true); }

?>