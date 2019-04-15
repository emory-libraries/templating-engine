<?php

// Strip erraneous slashes from a path string.
function cleanpath( $unclean ) {
  
  return preg_replace('/\/+/', '/', preg_replace('/\.\//', '', $unclean));
  
}

// Converts a relative path to an absolute path.
function absolute_path( $path ) {
  
  // Normalize directory separators.
  $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
  
  // Get absolute path parts.
  $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
  
  // Initialize the absolute path.
  $absolute = [];
  
  // Convert relative path parts into absolute path parts.
  foreach( $parts as $part ) {
    
    // Recognize `.` as the current directory.
    if( $path == '.' ) continue;
    
    // Recognize `..` as the previous directory.
    if( $path == '..' ) array_pop($absolute);
    
    // Otherwise, capture the path part.
    else $absolute[] = $part;
    
  }
  
  // Return the absolute path.
  return implode(DIRECTORY_SEPARATOR, $absolute);
  
}

// Converts a relative path to an absolute, root-relative path.
function absolute_path_from_root( $path ) { return cleanpath('/'.absolute_path($path)); }

?>