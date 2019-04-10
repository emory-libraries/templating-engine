<?php

// Initializes utility methods.
trait Cache_Utilities {
  
  // Get the target path of a file within the cache.
  private function __getCachePath( $path ) { 
    
    $path = str_replace($this->location.'/', '', $path);
    
    // Otherwise, get the path within the cache.
    return cleanpath("{$this->location}/{$path}"); 
  
  }
  
}

// Initialize filesystem methods.
trait Cache_Filesystem {
  
  // Check if a path leads to a file.
  private function __isFile( $path ) { return !is_dir($path); }
  
  // Check if a path leads to a directory.
  private function __isDirectory( $path ) { return is_dir($path); }
  
  // Check if a file exists.
  private function __fileExists( $file ) { 
    
    // Get the cache path.
    $path = $this->__getCachePath($file);
    
    // Verify that the path exists and the path leads to a file.
    return file_exists($path) and $this->__isFile($path);
  
  }
  
  // Check if a directory exists.
  private function __directoryExists( $directory ) { 
    
    // Get the cache path.
    $path = $this->__getCachePath($directory); 
    
    // Verify that the path exists and the path leads to a file.
    return file_exists($path) and $this->__isDirectory($path);
  
  }
  
  // Create a new directory.
  private function __newDirectory( $path, $permissions = 0775, $recursive = true ) {
    
    // Make sure the directory doesn't exist already.
    if( $this->__directoryExists($path) ) return;

    // Create the directory.
    mkdir($this->__getCachePath($path), $permissions, $recursive);
    
  }
  
  // Scan the contents of a directory.
  private function __scanDirectory( $path = '', $recursive = true ) { 
    
    // Verify that the path leads to an existing directory.
    if( !$this->__directoryExists($path) ) return false;
    
    // Scan recursively.
    if( $recursive ) return scandir_recursive($this->__getCachePath($path)); 
    
    // Otherwise, only scan the current level.
    return scandir_clean($this->__getCachePath($path));
  
  }
  
  // Save a file.
  private function __saveFile( $path, $data = '', $permissions = 0775 ) {
    
    // Create any directories if they don't exist.
    if( !$this->__directoryExists(dirname($path)) ) $this->__newDirectory(dirname($path));
    
    // Create the file.
    file_put_contents($this->__getCachePath($path), $data);
    
    // Assign the file permissions.
    chmod($this->__getCachePath($path), $permissions);
    
  }
  
  // Read a file.
  private function __readFile( $path ) { 
    
    // Verify that the file exists, then read it.
    if( $this->__fileExists($path) ) return file_get_contents($this->__getCachePath($path)); 
  
  }
  
  // Determine if a file `a` is newer than file `b`.
  private function __isNewer( $a, $b ) { 
    
    // Verify that both files exist.
    if( !$this->__fileExists($a) or !$this->__fileExists($b) ) return false;
    
    // Otherwise, return the file that is newer.
    return filemtime($a) > filemtime($b) ? $a : $b;
    
  }
  
}

// Creates a `Cache` class for caching data and files.
class Cache {
  
  // Loads traits.
  use Cache_Utilities, Cache_Filesystem;
  
  // Capture configurations.
  protected $config;
  
  // Capture's the cache location.
  private $location;
  
  // Constructor
  function __construct( $location ) {
    
    // Use global configurations.
    global $config;
    
    // Save the configurations.
    $this->config = $config;
    
    // Save the cache location.
    $this->location = $location;
    
    // Initialize the cache.
    $this->init();
    
  }
  
  // Initialize the cache.
  private function init() { 
   
    // Create the cache directory if it doesn't already exist.
    if( !$this->__directoryExists('') ) $this->__newDirectory('');
    
  }
  
  // Get something out of the cache.
  public function get( $path ) { return $this->__readFile($path); }
    
  // Add something to the cache.
  public function add( $path, $data, $permissions = 0777 ) { $this->__saveFile($path, $data, $permissions); }
  
  // Scan a directory within the cache.
  public function scan( $path, $recursive = true ) { return $this->__scanDirectory($path, $recursive); }
  
  // Determine if a file or directory can be found within the cache.
  public function has( $path ) { return $this->__fileExists($path) or $this->__directoryExists($path) ? true : false; }
  
  // Determine if a cached file is newer than a given file.
  public function newer( $cached, $comp ) { 
    
    // Get the cached file path.
    $cached = $this->__getCachePath($cached);
    
    // Check if the cached file is newer than its comparative.
    return $this->__isNewer($cached, $comp) == $cached;
  
  }
  
  // Determine if a cached file is older than a given file.
  public function older( $cached, $comp ) { 
    
    // Get the cached file path.
    $cached = $this->__getCachePath($cached);
    
    // Check if the cached file is newer than its comparative.
    return $this->__isNewer($cached, $comp) != $cached;
  
  }
  
  // Get the real path of a file or directory within the cache.
  public function path( $path ) {
    
    // Return the path if it's valid.
    return ($this->__fileExists($path) or $this->__directoryExists($path)) ? $this->__getCachePath($path) : false;
    
  }
  
}

?>