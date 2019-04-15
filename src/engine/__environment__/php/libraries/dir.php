<?php

function scandir_clean( $path ) {
  
  return array_values(array_filter(scandir($path), function($file) {
        
    return !in_array($file, ['.', '..', '.DS_Store']);

  }));
  
}

function scandir_recursive( $path, $prefix = '' ) {
  
  // Scan the contents of the directory.
  $contents = scandir_clean($path);
  
  // Initialize results.
  $results = [];

  // Recursively, scan subdirectories.
  foreach( $contents as $key => $file ) {  

    // Get the subdirectory path.
    $subdir = "$path/$file"; 

    // Determine if the subdirectory exists.
    if( is_dir($subdir) ) {

      // Continue scanning the subdirectory for files.
      $results = array_merge($results, scandir_recursive($subdir, "$file/"));

    }
    
    // Otherwise, a file was found.
    else {
      
      $results[] = "{$prefix}{$file}";
      
    }

  }
    
  // Return the results.
  return $results;
  
}

?>