<?php

namespace HandlebarsHelpers;

trait FsHelpers {
  
  // Read a file from the file system.
  public static function read( $path, $options ) {
    
    // Initialize the result.
    $contents = '';
    
    // Verify that the file exists, and read it.
    if( File::isFile($path) ) $contents = File::read($path);
    
    // Return the file contents.
    return $contents;
    
  }
  
  // Return an array of files from the given directory.
  public static function readdir( $directory, $filter ) { 
    
    // Capture filters.
    $arguments = func_get_args();
    $options = array_last($arguments); 
    $filter = func_num_args() == 3 ? $filter : false;
    
    // Get a list of all helpers.
    $helpers = Engine\API::get('/helpers');
    
    // Initialize the result.
    $contents = [];
    
    // Verify that the directory exists.
    if( File::isDirectory($directory) ) {
      
      // Get directory contents.
      $contents = Index::scan($directory);
      
      // Apply any filters if given.
      if( $filter ) {
      
        // Use a function filter.
        if( gettype($filter) == 'callable' ) $contents = array_filter($contents, $filter);
        
        // Use a helper filter.
        if( array_key_exists($filter, $helpers) ) $contents = array_filter($contents, $helpers[$filter]);
        
        // Use a regex filter.
        if( is_regex($filter) ) $contents = array_filter($contents, function($file) use ($filter) {
          
          return preg_match($filter, $file);
          
        });
        
        // Use a keyword filter.
        if( in_array($filter, ['isFile', 'isDirectory']) ) $contents = array_filter($contents, "File::$filter");
   
        // Otherwise, try to use a globbing filter.
        if( is_string($filter) ) { 
          
          // Find files based on the globbing pattern.
          $glob = glob("$directory/$filter");
          
          // Filter out globbed files.
          $contents = array_filter($contents, function($file) use ($glob) {
            
            return !in_array($file, $glob);
            
          });
          
        } 
      
      }
      
    }
    
    // Reset array indices.
    $contents = array_values($contents);
    
    // Return the directory contents.
    return $contents;
    
  }
  
  
}

?>