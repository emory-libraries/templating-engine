<?php

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

?>