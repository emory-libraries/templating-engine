<?php

// Converts a string into its attribute-friendly version.
function strtoattr( string $string, $delimiter = '-', $regex = "/[^A-Za-z0-9\/\ ]/" ) {
  
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

// Converts a string to a camelcase format, removing any delimiters and spaces.
function strtocamel( string $string, $delimiters = '-_ ' ) {
  
  // Convert the delimiters to a regex-friendly format.
  $delimiters = implode('|', array_map('preg_quote', str_split($delimiters)));

  // Extract the delimiters.
  $new = preg_split('/'.$delimiters.'/', $string);

  // Pull out the first part of the string.
  $first = array_shift($new); 
  
  // Capitalize all remaining parts of the string.
  $new = array_map('ucfirst', $new); 
  
  // Recombine and return string.
  return $first.implode('', $new);
  
}

?>