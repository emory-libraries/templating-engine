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
    $root = CONFIG['engine']['cache'];
    
    // Return the absolute path.
    return cleanpath("$root/".str_replace($root, '', trim($path, '/')));
    
  }
  
  // Write a file to the cache.
  public static function write( $path, $data ) {
    
    // Create the directory if it doesn't exist.
    if( !self::isDirectory(dirname($path)) ) self::make(dirname($path));
    
    // Write the file.
    File::write(self::path($path), $data);
    
  }
  
  // Read a file from the cache.
  public static function read( $path ) {
    
    // Read the file if it exists.
    if( self::isFile($path) ) return File::read(self::path($path));
    
  }
  
  // Include a file from the cache.
  public static function include( $path ) {
    
    // Get the absolute path within the cache.
    $path = self::path($path);
    
    // Include the file if it exists.
    if( file_exists($path) ) return include $path;
    
  }
  
  // Determines if a path exists within the cache.
  public static function exists( $path ) { return file_exists(self::path($path)); }
  
  // Determines if a path is an existing directory.
  public static function isDirectory( $path ) { return is_dir(self::path($path)); }
  
  // Determines if a path is an existing file.
  public static function isFile( $path ) { return is_file(self::path($path)); }
  
  // Create a directory at a given path.
  public static function make( $path, $mode = 0755, $recursive = true ) { 
    
    // Make directories using the given mode.
    return mkdir(self::path($path), $mode, $recursive); 
  
  }
  
  // Scans the contents of a directory.
  public static function scan( $path, $recursive = false ) {
    
    // Verify that the path is a directory, and scan it.
    if( self::isDirectory($path) ) return ($recursive ? scandir_recursive(self::path($path)) : scandir_clean(self::path($path)));
    
  }
  
  // Get the last modified time of a file.
  public static function modified( $path ) {
  
    // Verify that the file exists, and return the last modified time.
    if( self::isFile($path) ) return File::modified(self::path($path));
  
  }
  
  // Determine if a cached file is outdated based on a time of reference.
  public static function outdated( $path, $time ) {
    
    return (self::modified($path) < $time);
    
  }

}

?>