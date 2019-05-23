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
  
}

?>