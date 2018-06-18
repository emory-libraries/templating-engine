<?php

function scandir_recursive( $path, $prefix = '' ) {
  
  // Scan the contents of the directory.
  $contents = array_values(array_filter(scandir($path), function($file) {
        
      return !in_array($file, ['.', '..']);

    }));

  // Recursively, scan subdirectories.
  foreach( $contents as $key => $file ) { 

    $subpath = "$path/$file";

    if( is_dir($subpath) ) {

      $contents = array_merge(scandir_recursive($subpath, "$file/"));

    }
    
    else $contents[$key] = "{$prefix}{$file}";

  }
    
  // Return.
  return $contents;
  
}

?>