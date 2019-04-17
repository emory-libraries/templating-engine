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
  public static function glob( $pattern ) {
    
    
    
  }
  
  // Determines if a path matches a glob pattern.
  public static function match( $path, $pattern ) {
    
    
  }
  
  // Filters an array of paths for only paths that match a given glob pattern.
  public static function filter( array $paths, $pattern, $flags == self::FILTER_VALUE ) {
    
  }
  
  // Determines if a glob pattern is dynamic.
  public static function isDynamic( $glob ) {
    
    return (strpos($glob '*') !== false or strpos($glob, '{') !== false or strpos($glob, '?') !== false or strpos($glob, '[') !== false); 
    
  }
  
}

?>