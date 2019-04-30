<?php

/**
 * Glob
 *
 * Attempts to find and validate paths using globbing patterns.
 */
class Glob {
  
  // Filters by values.
  const FILTER_VALUE = 1;
  
  // Filters by keys.
  const FILTER_KEY = 2;
  
  // TODO: Create globbing library for `MatchHelpers`. Refer to [webmozart/glob](https://github.com/webmozart/glob/).
  
  // Expands a glob pattern into an array of paths.
  public static function glob( string $pattern ) {
    
    // Use static paths as is.
    if( !self::isDynamic($pattern) ) return (File::exists($pattern) ? [$pattern] : []);
    
    // Otherwise, expand dynamic paths.
    else {
      
      // Expand the pattern.
      $patterns = self::expand($pattern);

    }
    
  }
  
  // Determines if a path matches a glob pattern.
  public static function match( $path, $pattern ) {
    
    
  }
  
  // Filters an array of paths for only paths that match a given glob pattern.
  public static function filter( array $paths, $pattern, $flags = self::FILTER_VALUE ) {
    
  }
  
  // Expand a glob pattern.
  public static function expand( string $pattern ) {
    
    // Initialize a pattern set.
    $patterns = [];
    
    // Get the base path.
    $base = self::getBasePath($pattern);
    
    // Expand braces.
      if( preg_match_all('/\{(?P<alt>.+?)\}/i', $pattern, $braces)) {
        
        // Determine if alternatives exist.
        if( isset($braces['alt']) ) {
          
          // Get the alternatives.
          $alts = array_map(function($alt) {
            
            // Convert alternatives to an array.
            return array_map('trim', explode(',', $alt));
            
          }, $braces['alt']);
          
          // Get all combinations of alternatives.
          $combos = array_combos(...$alts);
          
          //echo "<pre>"; var_dump($combos); echo "</pre>";
          
        }
        
      }
    
  }
  
  // Get the base path of a glob pattern.
  public static function getBasePath( string $pattern ) {
    
    // Get the static path portion.
    $static = self::getStaticPath($pattern);
    
    // Find the base path within the static path.
    if( ($position = strrpos($static, '/')) !== false ) {
      
      // Identify root-level base paths.
      if( $position === 0 ) return '/';
      
      // Identify base paths from double-slash sequences.
      if( $position - 3 === strpos($pattern, '://') ) return substr($static, 0, $position + 1);
      
      // Otherwise, assume a generic base path can be used.
      return substr($static, 0, $position);
      
    }
      
    // Otherwise, assume the static path does not have a base path.
    return '';
    
  }
  
  // Get the static portion of a glob pattern.
  public static function getStaticPath( string $pattern ) {
    
    // Return the given pattern if it's already a static path.
    if( !self::isDynamic($pattern) ) return $pattern;
    
    // Initialize the path.
    $path = '';
    
    // Get the static portion of the glob pattern.
    for( $i = 0; $i < strlen($pattern); ++$i ) {
      
      // Get the current character.
      $char = $pattern[$i];
      
      // Determine if the character is dynamic.
      switch( $char ) {
          
        // Look for directory separators.
        case DIRECTORY_SEPARATOR:
          
          // Capture the directory separator.
          $path .= DIRECTORY_SEPARATOR;
          
          // Stop if this is the last static part of the path.
          if( isset($pattern[$i + 3]) and '**'.DIRECTORY_SEPARATOR === $pattern[$i + 1].$pattern[$i + 2].$pattern[$i + 3]) break 2;

          // Otherwise, break, and go to the next character.
          break;
          
        // Look for any dynamic characters.
        case '*':
        case '?':
        case '{':
        case '[':
          
          // Stop if found.
          break 2;
          
        // Look for any escaped sequences.
        case '\\':
          
          // Determine if a character is being escaped.
          if( isset($pattern[$i + 1]) ) {
            
            // Look for the escaped character.
            switch( $pattern[$i + 1] ) {
              
              // Handle escaped dynamic characters.
              case '*':
              case '?':
              case '{':
              case '[':
              case '\\':
                
                // Capture the dynamic character.
                $path .= $pattern[$i + 1];
                
                // Increment the index to skip to the next subsequent character.
                ++$i;
                
                // Break, and go to the next character.
                break;
                
              // Otherwise, handle escaped static characters.
              default:
                
                // Capture the escape sequence.
                $path .= '\\';
                
            }

          } 
          
          // Otherwise, assume there's nothing to escape, but capture the escape sequence.
          else $path .= '\\';
          
          // Break, and go to the next character.
          break;
          
        // Otherwise, assume the character is static.
        default:
          
          // Capture the static character.
          $path .= $char;
          
          // Break, and go to the next character.
          break;
          
      }
      
    }
    
    // Return the static path.
    return $path;
    
  }
  
  // Determines if a glob pattern is dynamic.
  public static function isDynamic( $glob ) {
    
    return (strpos($glob, '*') !== false or strpos($glob, '{') !== false or strpos($glob, '?') !== false or strpos($glob, '[') !== false); 
    
  }
  
  // Initialize a constructor to prevent error messages.
  function __construct() {}
  
}

?>