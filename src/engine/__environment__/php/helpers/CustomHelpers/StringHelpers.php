<?php

namespace CustomHelpers;

trait StringHelpers {
  
  // Combine, or concatenate, multiple strings together.
  public static function combine( ...$strings ) {
    
    // Remove the options from the strings, then concatenate all strings.
    return implode('', array_head($strings));
    
  }
  
  // Trim a substring from the start of a string.
  public static function trimSubstringStart( string $string, string $substring, $modifiers ) {
    
    // Set the default modifier to be multiline.
    $modifiers = func_num_args() > 3 ? $modifiers : 'm';
    
    // Trim the substring from the start of the string.
    return ltrim_substr($string, $substring, $modifiers);
    
  }
  
  // Trim a substring from the end of a string.
  public static function trimSubstringEnd( string $string, string $substring, $modifiers ) {
    
    // Set the default modifier to be multiline.
    $modifiers = func_num_args() > 3 ? $modifiers : 'm';
    
    // Trim the substring from the end of the string.
    return rtrim_substr($string, $substring, $modifiers);
    
  }
  
  // Trim a substring from the start and end of a string.
  public static function trimSubstring( string $string, string $substring, $modifiers ) {
    
    // Set the default modifier to be multiline.
    $modifiers = func_num_args() > 3 ? $modifiers : 'm';
    
    // Trim the substring from the start of the string.
    return trim_substr($string, $substring, $modifiers);
    
  }
  
  // Encode a string to use HTML character codes as needed.
  public static function encodeHTML( string $string ) {
    
    return htmlentities($string);
    
  }
  
  // Decode a string using HTML character codes.
  public static function decodeHTML( string $string ) {
    
    return html_entity_decode($string);
    
  }
  
  // Generate a unique ID.
  public static function uid( $prefix ) {
    
    // Use an empty prefix is none was given.
    $prefix = is_string($prefix) ? $prefix : '';
    
    // Return a unique ID with the prefix prepended.
    return uniqid($prefix, true);
    
  }
  
  // Determine if a string starts with another substring.
  public static function startsWithSubstring( $string, $substring, $options ) {
    
    // Determine if the string starts with the substring.
    return str_starts_with($string, $substring);
    
  }
  
  // Determine if a string ends with another substring.
  public static function endsWithSubstring( $string, $substring, $options ) {
    
    // Determine if the string starts with the substring.
    return str_ends_with($string, $substring);
    
  }
  
}

?>