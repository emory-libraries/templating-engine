<?php

// Convert a string to kebabcase.
function kebabcase( string $string ) {
  
  // Make sure the string is lowercase.
  $string = strtolower($string);

  // Extract words in the string.
  $words = array_values(array_filter(preg_split('/-|_| /', $string)));
  
  // Remove extraneous characters from words.
  $words = array_map(function($word) {
    
    // Trim extraneous characters.
    return preg_replace('/^[^a-z0-9]|[^a-z0-9]$/', '', $word);
    
  }, $words);
  
  // Return the kebabcase string.
  return implode('-', $words);

}

// Convert an array key to kebabcase.
function kebabcase_key( string $key ) {
  
  // Assume the key is in dot notation, and slugify each part.
  return implode('.', array_map('kebabcase', explode('.', $key)));
  
}

// Convert a string to camelcase.
function camelcase( string $string ) { 
  
  // Make sure the string is lowercase.
  $string = strtolower($string);

  // Extract words in the string.
  $words = array_values(array_filter(preg_split('/-|_| /', $string)));
  
  // Remove extraneous characters from words.
  $words = array_map(function($word) {
    
    // Trim extraneous characters.
    return preg_replace('/^[^a-z0-9]|[^a-z0-9]$/', '', $word);
    
  }, $words);

  // Get the first word.
  $first = array_shift($words);
  
  // Capitalize the remaining words.
  $words = array_map('ucfirst', $words);
  
  // Return the camelcase string.
  return $first.implode('', $words);

}

// Convert an array key to camelcase.
function camelcase_key( string $key ) {
  
  // Assume the key is in dot notation, and camelcase each part.
  return implode('.', array_map('camelcase', explode('.', $key)));
  
}

// Determines if a string starts with another string.
function str_starts_with( string $string, string $target, int $position = null ) {
  
  // Get the string length.
  $length = strlen($string);
  
  // Use the length as position if not given.
  $position = null === $position ? 0 : +$position;
  
  // Get the position from the start or end.
  if( $position < 0 ) $position = 0;
  else if( $position > $length ) $position = $length;
  
  // Determine if the string starts with the target string.
  return ($position >= 0 and substr($string, $position, strlen($target)) === $target);
  
}

// Determines if a string ends with another string.
function str_ends_with( string $string, string $target, int $position = null ) {
  
  // Get the string length.
  $length = strlen($string);
  
  // Use the length as position if not given.
  $position = null === $position ? $length : +$position;
  
  // Get the position from the start or end.
  if( $position < 0 ) $position = 0;
  else if( $position > $length ) $position = $length;
  
  // Get the start position of the target string.
  $position -= strlen($target);
  
  // Determine if the string ends with the target string.
  return ($position >= 0 and substr($string, $position, strlen($target)) === $target);
  
}

?>