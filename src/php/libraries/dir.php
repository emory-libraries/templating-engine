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

    $subpath = "$path/$file";

    if( is_dir($subpath) ) {

      $results = array_merge($results, scandir_recursive($subpath, "$file/"));

    }
    
    else {
      
      $results[] = "{$prefix}{$file}";
      
    }

  }
    
  // Return.
  return $results;
  
}

?>