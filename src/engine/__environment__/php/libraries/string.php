<?php

use __ as _;

// Convert a string to kebabcase.
function kebabcase( string $string ) { return _::kebabCase($string); }

// Convert an array key to kebabcase.
function kebabcase_key( string $key ) {
  
  // Assume the key is in dot notation, and slugify each part.
  return implode('.', array_map('kebabcase', explode('.', $key)));
  
}

// Convert a string to camelcase.
function camelcase( string $string ) { return _::camelCase($string); }

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