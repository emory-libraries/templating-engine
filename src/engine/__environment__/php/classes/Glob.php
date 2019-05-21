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
  
  // Defines regular expressions for globbing patterns.
  public static $braces = '/\{(?P<alt>.+?)\}/i';
  public static $asterisks = '/\*{2}/';
  
  // Expands a glob pattern into an array of paths.
  public static function glob( string $pattern ) {
    
    // Use static paths as is.
    if( !self::isDynamic($pattern) ) return (File::exists($pattern) ? [$pattern] : []);
    
    // Otherwise, expand dynamic paths, and return the paths.
    return Glob::expand($pattern, true);
    
  }
  
  // Determines if a path matches a glob pattern.
  public static function match( $path, $pattern ) {
    
    // Expand the pattern to globs.
    $globs = Glob::expand($pattern);
    
    // Convert all globs to a regular expression.
    $globs = array_map('Glob::toRegex', $globs);
    
    // Determine if the path matches any of the glob expressions.
    return array_some($globs, function($regex) use ($path) {
      
      // Test whether or not the path matches the glob.
      return (preg_match($regex, $path) === 1);
      
    });
    
  }
  
  // Filters an array of paths for only paths that match a given glob pattern.
  public static function filter( array $paths, $pattern, $flags = Glob::FILTER_VALUE ) {
    
    // Get all globs for the pattern.
    $globs = Glob::glob($pattern);
    
    // Set the filter function based on the flag.
    $filter = ($flags & Glob::FILTER_KEY) ? 'array_filter_key' : 'array_filter';
    
    // Return the intersection between the path values and globs.
    return $filter($paths, function($path) use ($globs) {
      d($path, $globs, in_array($path, $globs));
      // Filter out any paths not in the glob set.
      return in_array($path, $globs);
      
    });
    
  }
  
  // Expand a glob pattern.
  public static function expand( string $pattern, $glob = false ) {
    
    // Initialize a pattern set.
    $patterns = [$pattern];
    
    // Get the base path.
    $base = self::getBasePath($pattern);
    
    // Initialize helpers for detecting globbing patterns.
    $hasBraces = function( string $pattern ) { 
      
      // Detect braces within the pattern.
      return (preg_match(Glob::$braces, $pattern) === 1); 
    
    };
    $hasAsterisks = function( string $pattern ) {
      
      // Detect brackets within the pattern.
      return (preg_match(Glob::$asterisks, $pattern) === 1); 
      
    };
    
    // Initialize helpers for expanding globbing patterns.
    $expandBraces = function( string $pattern ) {
      
      // Initialize the result.
      $result = [];
      
      // Find braces.
      preg_match_all(Glob::$braces, $pattern, $braces);

      // Get the alternatives.
      $alts = array_map(function($alt) {

        // Convert alternatives to an array.
        return array_map('trim', explode(',', $alt));

      }, $braces['alt']);

      // Get all combinations and permutations of the alternatives.
      $combos = array_combos(...$alts);
      
      // Determine if the combinations are multidimensional.
      $multi = count(array_filter($combos, 'is_array')) > 0;
      
      // Handle multidimensional arrays, meaning multiple sets of combinations were found.
      if( $multi ) {

        // Build patterns with the combinations.
        foreach( $combos as $combo ) {

          // Initialize the pattern variant.
          $variant = $pattern;

          // Merge the combination in the variant.
          foreach( $combo as $i => $alt ) {

            // Replace the alternatives within the given combination into the variant.
            $variant = str_replace($braces[0][$i], $alt, $variant);

          }

          // Save the pattern variant.
          $result[] = $variant;

        }
        
      }
      
      // Otherwise, handle simple arrays, meaning only one set of combinations was found.
      else {

        // Merge the combination in the variant.
        foreach( $combos as $i => $alt ) {
          
          // Initialize the pattern variant.
          $variant = $pattern;

          // Replace the alternatives within the given combination into the variant.
          $variant = str_replace($braces[0][0], $alt, $variant);
          
          // Save the pattern variant.
          $result[] = $variant;

        }

        
      }
      
      // Return the result.
      return $result;
      
    };
    $expandAsterisks = function( string $pattern ) {
      
      // Initialize the result.
      $result = [];
      
      // Split the pattern on its asterisks.
      $split = preg_split(Glob::$asterisks, $pattern, 2);
      
      // Get the base path and following path.
      $base = $split[0];
      $after = $split[1];
      
      // Resolve the directory path relative to the current working directory of the project.
      $dir = Path::resolveServer($base);
      
      // Determine if the asterisk is followed by a globstar, which would make it unfiltered.
      $filtered = explode('/', trim($after, '/'))[0] != '*';
    
      // Get the directories within the given folder.
      $directories = array_reduce(Index::scan($dir), function($result, $path) {
        
        // Get the path's directory.
        $dir = dirname($path);
        
        // Capture only directires.
        if( !in_array($dir, $result) ) $result[] = $dir;
        
        // Continue reducing.
        return $result;
        
      }, []);
      
      // For filtered expansions, filter out any directories that don't match the path sequence.
      if( $filtered ) {
        
        // Get the filter substring and after substring.
        $filter = rtrim(substr($after, 0, strpos($after, '*')), '/');
        $after = substr($after, strpos($after, '*'));
        
        // Filter the directories.
        $directories = array_values(array_filter($directories, function($path) use ($dir, $filter) {
        
          // Get the path endpoint without the directory root.
          $endpoint = str_replace(rtrim($dir, '/'), '', $path);

          // Filter out directories that don't match the path sequence.
          return (strpos($endpoint, $filter) !== false);

        }));
        
      }
      
      // If no directories were found, then use the base directory.
      if( empty($directories) ) {
        
        // Use the base directory and the remaining path.
        $result[] = rtrim($base, '/').'/'.ltrim($split[1], '/');
        
      }
      
      // Otherwise get pattern variants with each directory.
      else {
        
        // Build out directory variants.
        foreach( $directories as $directory ) {

          // Initialize the variant.
          $variant = $directory.'/'.ltrim($after, '/');

          // Save the variant.
          $result[] = $variant;

        }
        
      }
      
      // Return the result.
      return $result;
      
    };
    
    // Initialize a flag to indicate that expansion has not yet completed.
    $expanded = false;
    
    // Recursively expand the pattern until the pattern is fully expanded.
    while( !$expanded ) {

      // Reset the pattern indices, and remove empty values.
      $patterns = array_values(array_filter($patterns, function($pattern) {
        
        // Remove empty patterns.
        return isset($pattern);
        
      }));
      
      // Count the patterns.
      $count = count($patterns);
      
      // Continue to expand while applicable.
      if( $count > 0 ) {
      
        // Initialize an index.
        $i = 0;

        // Loop through the patterns and expand them.
        for( $i; $i < $count; $i++ ) {

          // Find braces and expand them.
          if( $hasBraces($patterns[$i]) ) {

            // Add the expanded patterns onto the patterns array.
            $patterns = array_merge($patterns, $expandBraces($patterns[$i]));

            // Unset the current pattern.
            unset($patterns[$i]);

          }

          // Otherwise, find astrerisks and expand them.
          else if( $hasAsterisks($patterns[$i]) ) {

            // Add the expanded patterns onto the patterns array.
            $patterns = array_merge($patterns, $expandAsterisks($patterns[$i]));

            // Then, unset the current pattern.
            $patterns[$i] = null;

          }

          // Otherwise, assume that all patterns have been expanded.
          else $expanded = true;

        }
        
      }
      
      // Otherwise, assume there's nothing else to expand.
      else $expanded = true;
      
    }
    
    // Reset the pattern indices.
    $patterns = array_values($patterns);
    
    // Return the expanded patterns with or without globbing them.
    return ($glob ? array_reduce($patterns, function($result, $pattern) {
      
      // Glob the pattern.
      $globs = glob($pattern);
      
      // Merge the globs into the result.
      $result = array_merge($result, $globs);
      
      // Continue reducing.
      return $result;
      
    }, []) : $patterns);
    
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
  
  // Convert an expanded glob pattern to a regular expression.
  public static function toRegex( string $glob, $insensitive = true ) {
    
    // Convert the glob to a regex.
    $regex = preg_replace_callback('/[\\\\^$.[\\]|()?*+{}\\-\\/]/', function($matches) {
      switch ($matches[0]) {
        case '*':
          return '.*';
        case '?':
          return '.';
        default:
          return '\\'.$matches[0];
      }
    }, $glob);
    
    // Return the regex.
    return '/^'.$regex.'$/'.($insensitive ? 'i' : '');
    
  }
  
  // Initialize a constructor to prevent error messages.
  function __construct() {}
  
}

?>