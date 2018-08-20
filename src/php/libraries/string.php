<?php

// Converts a string into its attribute-friendly version.
function str_attr( $string, $delimiter = '-', $regex = "/[^A-Za-z0-9\/\ ]/" ) {
  
  // Force the string to be lowercase.
  $string = strtolower($string);
  
  // Remove all special characters from the string, and trim whitespace.
  $string = trim(preg_replace($regex, '', $string));
  
  // Replace all spaces and forward slashes in the string with a dash.
  $string = preg_replace('/[\/\ ]/', $delimiter, $string);
  
  // Eliminate all duplicate delimiters within the string.
  $string = preg_replace("/{$delimiter}+/", $delimiter, $string);
  
  // Remove numbers from the start of the string.
  $string = preg_replace('/^[0-9]+/', '', $string);
  
  // Return the cleaned string.
  return $string;
  
}

?>