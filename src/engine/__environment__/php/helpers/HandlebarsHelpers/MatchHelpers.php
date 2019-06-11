<?php

namespace HandlebarsHelpers;

trait MatchHelpers {
  
  // Returns truthy if a file path contains the given pattern.
  public static function isMatch( $files, $patterns, $options ) {
    
    // Initialize the result.
    $result = false;
    
    // Force files and patterns into arrays.
    $files = is_array($files) ? $files : [$files];
    $patterns = is_array($patterns) ? $patterns : [$patterns];
    
    // Look for file matches.
    foreach( $files as $file ) {
      
      // Look for pattern matches.
      foreach( $patterns as $pattern ) {
        
        // Determine if the file and pattern matches.
        if( Glob::match($file, $pattern) ) {
          
          // Save the result.
          $result = true;
          
          // Break all loops.
          break 2;
          
        }
        
      }
      
    }
    
    // Return the result.
    return $result;
    
  }
  
  // Returns an array of paths that match the given glob pattern(s).
  public static function match( $files, $patterns, $options ) {
    
    // Initialize the result.
    $result = [];
    
    // Force files and patterns into arrays.
    $files = is_array($files) ? $files : [$files];
    $patterns = is_array($patterns) ? $patterns : [$patterns];
      
    // Look for pattern matches.
    foreach( $patterns as $pattern ) {

      // Capture the intersection between files and patterns.
      $result = array_merge($result, Glob::filter($files, $pattern));
      
    }
    
    // Return the result.
    return $result;
    
  }
  
}

?>