<?php

/*
 * Cache
 *
 * Reads, writes, and retrieves files from the cache.
 */
class Cache {
  
  // Get the absolute path within the cache.
  public static function path( $path ) {
    
    // Get the root path of the cache.
    $root = CONFIG['engine']['cache']['root'];
   
    // Return the absolute path.
    return cleanpath("$root/".trim(str_replace($root, '', $path), '/'));
    
  }
  
  // Write a file to the cache.
  public static function write( $path, $data ) {
    
    // Create the directory if it doesn't exist.
    if( !Cache::isDirectory(dirname($path)) ) Cache::make(dirname($path));
    
    // Write the file.
    return File::write(Cache::path($path), $data);
    
  }
  
  // Read a file from the cache.
  public static function read( $path ) {
    
    // Read the file if it exists.
    if( Cache::isFile($path) ) return File::read(Cache::path($path));
    
  }
  
  // Delete a file or folder from the cache.
  public static function delete( $path ) {
    
    // Delete a folder.
    if( Cache::isDirectory($path) ) return rmdir($path);
      
    // Otherwise, delete a file.
    return File::delete($path);
    
  }
  
  // Include a file from the cache.
  public static function include( $path ) { 
    
    // Get the absolute path within the cache.
    $path = Cache::path($path);
    
    // Include the file if it exists.
    if( Cache::exists($path) ) return (include $path);
    
  }
  
  // Determines if a path exists within the cache.
  public static function exists( $path ) { return file_exists(Cache::path($path)); }
  
  // Determines if a path is an existing directory.
  public static function isDirectory( $path ) { return is_dir(Cache::path($path)); }
  
  // Determines if a path is an existing file.
  public static function isFile( $path ) { return is_file(Cache::path($path)); }
  
  // Create a directory at a given path.
  public static function make( $path, $mode = 0755, $recursive = true ) { 
    
    // Make directories using the given mode.
    return mkdir(Cache::path($path), $mode, $recursive); 
  
  }
  
  // Scans the contents of a directory.
  public static function scan( $path, $recursive = false ) {
    
    // Verify that the path is a directory, and scan it.
    if( Cache::isDirectory($path) ) return ($recursive ? scandir_recursive(Cache::path($path)) : scandir_clean(Cache::path($path)));
    
  }
  
  // Get the last modified time of a file.
  public static function modified( $path ) {
  
    // Verify that the file exists, and return the last modified time.
    if( Cache::isFile($path) ) return File::modified(Cache::path($path));
  
  }
  
  // Determine if a cached file is outdated based on a time of reference.
  public static function outdated( $path, $time ) {
    
    return (Cache::modified($path) < $time);
    
  }
  
  // Temporarily store a file in the cache.
  public static function tmp( $data, $path = null ) {
    
    // Get the temporary cache path.
    $tmp = Cache::path('/tmp');
    
    // Ensure that the temporary cache directory exists.
    if( !Cache::isDirectory($tmp) ) Cache::make($tmp);
    
    // Get the temporary file path.
    $path = isset($path) ? cleanpath($tmp.'/'.$path) : tempnam($tmp, 'eul_');
    
    // Write the data to the file.
    Cache::write($path, $data);
    
    // Return the file's path with helper methods to manipulate the file as needed.
    return [
      'path' => $path,
      'read' => function() use ($path) {
        
        // Read the file.
        return Cache::read( $path );
        
      },
      'write' => function( $data ) use ($path) {
        
        // Write to the file.
        return Cache::write($path, $data);
        
      },
      'include' => function() use ($path) {
        
        // Include the file.
        return Cache::include($path);
        
      },
      'delete' => function() use ($path) {
        
        // Delete the temporary file.
        return Cache::delete($path);
        
      }
    ];
    
  }
  
}

?>