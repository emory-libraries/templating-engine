<?php

function scandir_clean( $path ) {
  
  return array_values(array_filter(scandir($path), function($file) {
        
    return !in_array($file, ['.', '..']);

  }));
  
}

function scandir_recursive( $path, $prefix = '' ) {
  
  // Scan the contents of the directory.
  $contents = scandir_clean($path);

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