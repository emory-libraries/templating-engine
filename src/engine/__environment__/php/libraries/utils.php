<?php

// Returns the first real value within a list of values.
function fallback( ...$values ) {

  foreach( $values as $value ) {

    if( isset($value) ) return $value;

  }

  return $values[count($values) - 1];

}

// Finds the index of somethign within an array or string.
function index_of( $needle, $haystack ) {

  // Make sure the haystack is something that can be searched, otherwise, automatically return false.
  if( !is_string($haystack) and !is_array($haystack) ) return false;

  // Find the index of the needle within the haystack.
  return is_string($haystack) ? strpos($haystack, $needle) : array_search($needle, $haystack);

}

?>
