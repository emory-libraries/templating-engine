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
  public static function endpoint( $path, $base = [
    CONFIG['data']['site']['root'],
    CONFIG['data']['environment']['root'],
    CONFIG['patterns']['root'],
    CONFIG['engine']['meta']
  ] ) {
    
    // Get the file's directory path.
    $directory = dirname($path);
    
    // Remove data and patterns paths from the directory.
    foreach( $base as $remove ) { $directory = str_replace($remove, '', $directory); }
   
    // Convert the directory to kebabcase.
    $directory = implode('/', array_map('kebabcase', explode('/', $directory)));
    
    // Get the ID from the dirname and basename in kebabcase.
    return (isset($directory) ? "$directory/" : "").File::id($path);
    
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
  
  // Write a file.
  public static function write( $path, $data = null ) {
    
    // Write a file.
    return file_put_contents($path, (string) $data);
    
  }
  
  // Delete a file.
  public static function delete( $path ) {
    
    // Delete a file.
    return unlink($path);
    
  }
  
  // Get the last modified time of a file.
  public static function modified( $path ) {
    
    // Return the file's last modified time.
    return filemtime($path);
    
  }
  
}

?>