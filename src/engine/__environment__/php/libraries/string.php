<?php

// Convert a string to kebabcase.
function kebabcase( string $string ) {
  
  // Make sure the string is lowercase.
  $string = strtolower($string);

  // Extract words in the string.
  $words = array_values(array_filter(preg_split('/-|_| |\./', $string)));
  
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

// Convert a string to snakecase.
function snakecase( string $string ) {
  
  // Make sure the string is lowercase.
  $string = strtolower($string);

  // Extract words in the string.
  $words = array_values(array_filter(preg_split('/-|_| |\./', $string)));
  
  // Remove extraneous characters from words.
  $words = array_map(function($word) {
    
    // Trim extraneous characters.
    return preg_replace('/^[^a-z0-9]|[^a-z0-9]$/', '', $word);
    
  }, $words);
  
  // Return the kebabcase string.
  return implode('_', $words);

}

// Convert an array key to snakecase.
function snakecase_key( string $key ) {
  
  // Assume the key is in dot notation, and slugify each part.
  return implode('.', array_map('snakecase', explode('.', $key)));
  
}

// Convert a string to camelcase.
function camelcase( string $string ) { 
  
  // Make sure the string is lowercase.
  $string = strtolower($string);

  // Extract words in the string.
  $words = array_values(array_filter(preg_split('/-|_| |\./', $string)));
  
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

// Convert a string to dotcase.
function dotcase( string $string ) {
  
  // Make sure the string is lowercase.
  $string = strtolower($string);

  // Extract words in the string.
  $words = array_values(array_filter(preg_split('/-|_| |\./', $string)));
  
  // Remove extraneous characters from words.
  $words = array_map(function($word) {
    
    // Trim extraneous characters.
    return preg_replace('/^[^a-z0-9]|[^a-z0-9]$/', '', $word);
    
  }, $words);
  
  // Return the kebabcase string.
  return implode('.', $words);

}

// Convert a string to pathcase.
function pathcase( string $string ) {
  
  // Make sure the string is lowercase.
  $string = strtolower($string);

  // Extract words in the string.
  $words = array_values(array_filter(preg_split('/-|_| |\./', $string)));
  
  // Remove extraneous characters from words.
  $words = array_map(function($word) {
    
    // Trim extraneous characters.
    return preg_replace('/^[^a-z0-9]|[^a-z0-9]$/', '', $word);
    
  }, $words);
  
  // Return the kebabcase string.
  return implode(DIRECTORY_SEPARATOR, $words);

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

// Replace the nth occurrence of a substring within another string.
function str_replace_nth( string $needle, string $replacement, string $haystack, int $nth ) {
  
  // Split the string on the needle.
  $split = explode($needle, $haystack);
  
  // Get the values before and after the needle's nth occurrence.
  $before = array_slice($split, 0, ($nth < 0 ? count($split) + $nth : $nth));
  $after = array_slice($split, $nth);
  
  // Join the unaffected strings with the needle.
  $before = implode($needle, $before);
  $after = implode($needle, $after);
  
  // Finally, join the before and after values with the replacement, and return the string.
  return $before.$replacement.$after;
  
}

// Replace the first occurrence of a substring within another string.
function str_replace_first( string $needle, string $replacement, string $haystack ) {
  
  // Replace the first occurrence.
  return str_replace_nth($needle, $replacement, $haystack, 1);
  
}

// Replace the last occurrence of a substring within another string.
function str_replace_last( string $needle, string $replacement, string $haystack ) {
  
  // Replace the last occurrence.
  return str_replace_nth($needle, $replacement, $haystack, -1);
  
}

?>