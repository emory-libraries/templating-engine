<?php

/**
 * File
 *
 * Extracts data about a file path.
 */
class File {
  
  // Get a file's ID.
  public static function id( $path ) {
    
    // Return the basename without its extension.
    return kebabcase(basename($path, '.'.pathinfo($path, PATHINFO_EXTENSION)));
    
  }
  
  // Get a file's endpoint.
  public static function endpoint( $path ) {
    
    // Get the file's directory path.
    $directory = dirname($path);
    
    // Remove data and patterns paths from the directory.
    foreach( [
      CONFIG['data']['site']['root'],
      CONFIG['data']['environment']['root'],
      CONFIG['patterns']['root'],
      CONFIG['engine']['meta']
    ] as $remove ) { $directory = str_replace($remove, '', $directory); }
   
    // Convert the directory to kebabcase.
    $directory = implode('/', array_map('kebabcase', explode('/', $directory)));
    
    // Get the ID from the dirname and basename in kebabcase.
    return (isset($directory) ? "$directory/" : "").self::id($path);
    
  }
  
  // Read a file or array of files.
  public static function read( $path, $recursive = true ) {
    
    // Read a single file.
    if( is_string($path) ) return file_get_contents($path);
    
    // Otherwise, read an array of files.
    else if( is_array($path) ) {
      
      // Traverse the file list.
      foreach( $path as $index => $file ) {
        
        // Recursively read all files within nested arrays if the recursive flag is set.
        if( is_array($file) and $recursive ) $path[$index] = File::read($file, $recursive);
        
        // Otherwise, read the file from its path.
        else $path[$index] = file_get_contents($file);
        
      }
      
      // Return the list of files.
      return $path;
      
    }
    
  }
  
}

?>