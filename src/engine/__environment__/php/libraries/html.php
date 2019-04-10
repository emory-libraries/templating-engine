<?php

// Convert an associative array to an array of CSS style declarations.
function array_to_css( array $array, $inline = true ) {
  
  // Initialize the result.
  $result = [];
  
  // Convert associative array to CSS styles.
  foreach( $array as $property => $value ) { array_push($result, "$property: $value"); }
  
  // Return the result.
  return ($inline ? 'style="'.trim(implode('; ', $result)).'"' : trim(implode('; ', $result)));
  
}

// Convert an associative array to an arry of HTML attributes.
function array_to_attr( array $array ) {
  
  // Initialize the result.
  $result = [];
  
  // Convert associative array to HTML attributes.
  foreach( $array as $attribute => $value ) {
    
    array_push($result, $attribute.'="'.preg_replace('/^[\'\"]|[\'\"]$/', '', $value).'"');
    
  }
  
  // Return the result.
  return implode(' ', $result);
  
}

?>