<?php

// Splits a string into its words.
function words( string $string, string $pattern = null ) {
  
  if( isset($pattern) ) {
    
    if( preg_match_all($pattern, $string, $matches) > 0 ) return $matches[0];
    
  }
  
  return explode(' ', trim($string, ',;:."!?(){}[]<>_- '));
  
}

// Convert a string to kebabcase.
function kebabcase( string $string ) {
  
  return implode('-', array_map('strtolower', words(preg_replace("/['\x{2019}]/u", '', $string))));

}

// Convert an array key to kebabcase.
function kebabcase_key( string $key ) {
  
  // Assume the key is in dot notation, and slugify each part.
  return implode('.', array_map('kebabcase', explode('.', $key)));
  
}

// Convert a string to camelcase.
function camelcase( string $string ) { 
  
  return lcfirst(array_reduce(words(preg_replace("/['\\x{2019}]/u", '', $string)), function ($result, $word) {
    
    return $result.ucfirst(strtolower($word));
    
  }, ''));

}

// Convert an array key to camelcase.
function camelcase_key( string $key ) {
  
  // Assume the key is in dot notation, and camelcase each part.
  return implode('.', array_map('camelcase', explode('.', $key)));
  
}

// Determines if a string engins with another string.
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