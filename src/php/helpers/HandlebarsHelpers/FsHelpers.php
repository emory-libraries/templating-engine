<?php

namespace HandlebarsHelpers;

use _;

trait FsHelpers {
  
  // Read a file from the file system.
  public static function read( $path, $options ) {
    
    // Use global configurations.
    global $config;
    
    // Initialize the result.
    $contents = '';
    
    // Verify that the file exists.
    if( file_exists($config->DATA.'/'.$path) and is_file($config->DATA.'/'.$path) ) {
      
      // Read the file,
      $contents = file_get_contents($config->DATA.'/'.$path);
      
    }
    
    // Return the file contents.
    return $contents;
    
  }
  
  // Return an array of files form the given directory.
  public static function readdir( $directory, $filter ) {
    
    // Capture filters.
    $arguments = func_get_args();
    $options = _::last($arguments);
    $filter = func_num_args() == 3 ? $filter : false;
    
    // Use global configurations.
    global $config;
    global $HELPERS;
    
    // Initialize the result.
    $contents = [];
    
    // Verify that the directory exists.
    if( file_exists($config->DATA.'/'.$directory) and is_dir($config->DATA.'/'.$directory) ) {
      
      // Get directory contents.
      $contents = array_map(function($file) use ($directory) {
        
        return stripslashes($directory.'/'.$file);
        
      }, scandir_clean($config->DATA.'/'.$directory));
      
      // Apply any filters if given.
      if( $filter ) {
      
        // Use a function filter.
        if( gettype($filter) == 'callable' ) $contents = array_filter($contents, $filter);
        
        // Use a helper filter.
        if( array_key_exists($filter, $HELPERS) ) $contents = array_filter($contents, $HELPERS[$filter]);
        
        // Use a regex filter.
        if( is_regex($filter) ) $contents = array_filter($contents, function($file) use ($filter) {
          
          return preg_match($filter, $file);
          
        });
        
        // Use a keyword filter.
        if( in_array($filter, ['isFile', 'isDirectory']) ) $contents = array_filter($contents, ($filter == 'isFile' ? 'is_file' : 'is_dir'));
   
        // Otherwise, try to use a globbing filter.
        if( is_string($filter) ) { 
          
          // Find files based on the globbing pattern.
          $glob = array_map(function($glob) use ($config) {
            
            return str_replace($config->DATA.'/', '', $glob);
            
          }, glob($config->DATA.'/'.$directory.'/'.$filter));
          
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