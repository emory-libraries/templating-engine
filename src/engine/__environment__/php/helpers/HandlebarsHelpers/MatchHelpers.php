<?php

namespace HandlebarsHelpers;

use _;
use Webmozart\Glob;

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
        
        // TODO: Finish definition for `isMatch` helper. Needs `Glob` library.
        
        // Determine if the file and pattern matches.
        /*if( ... ) {
          
          // Save the result.
          $result = true;
          
          // Break all loops.
          break 2;
          
        }*/
        
      }
      
    }
    
    // Return the result.
    return $result;
    
  }
  
  // TODO: Create definition for `match` helper.
  
}

?>