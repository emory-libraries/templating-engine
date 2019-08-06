<?php

/*
 * Cache
 *
 * Reads, writes, and retrieves files from the cache.
 */
class Cache {

  // The cache path used by the cache instance.
  protected $path;

  // Defines expiry time constants.
  const CACHE_EXPIRES_MINUTE = 60;
  const CACHE_EXPIRES_HOUR = 3600;
  const CACHE_EXPIRES_DAY = 86400;

  // Constructs the cache.
  function __construct( string $path = CONFIG['engine']['cache']['cache'] ) {

    // Save the cache path to be used by the cache instance.
    $this->path = self::path($path);

    // Caching is disabled, then reset the cache.
    if( !CACHING ) $this->reset();

  }

  // Get all data from the cache.
  public function all() {

    // Scan the contents of the cache.
    $keys = static::scan($this->path);

    // Get all items within the cache.
    $cached = array_map($this->get, $keys);

    // Initialize the result.
    $result = [];

    // Get all cached data.
    foreach( $keys as $i => $key ) { $result[strtr($key, '/', '.')] = $cached[$i]; }

    // Return all cached data.
    return $result;

  }

  // Initialize some new cache data.
  public function new( $value, $expires = Cache::CACHE_EXPIRES_HOUR ) {

    // Get the expiry time, or disable expiration altogether in development mode.
    $expires = DEVELOPMENT ? INF : (time() + $expires);

    // Initialize some new cache data, but don't cache it.
    $new = [
      'data' => $value,
      'expires' => $expires
    ];

    // Return the new cache data.
    return $new;

  }

  // Get some data from the cache.
  public function get( $key, $default = null ) {

    // Verify that the item exists within the cache, and immediately return the default if not.
    if( !$this->has($key) ) return $default;

    // Get the item from the cache.
    $item = (include $this->resolve($key));

    // Verify the the item is not expired.
    if( !$this->expired($item) ) return $item['data'];

    // Otherwise, remove the item in the cache.
    else $this->unset($key);

    // Return the default by default.
    return $default;

  }

  // Set some data within the cache.
  public function set( $key, $value, $expires = Cache::CACHE_EXPIRES_HOUR ) {

    // Get the expiry time, or disable expiration altogether in development mode.
    $expires = DEVELOPMENT ? INF : (time() + $expires);

    // Get a copy of the cache data, if available.
    $data = $this->get($key, []);

    // Set the value.
    $value = [
      'data' => $value,
      'expires' => $expires
    ];

    // Set the given value within the cache data.
    $data = array_merge($data, $value);

    // Update the cache.
    return $this->save($key, $data);

  }

  // Unset some data within the cache.
  public function unset( $key ) {

    // Unset the given key.
    return unlink($this->resolve($key));

  }

  // Determine if the cache has the given item.
  protected function has( string $key ) {

    // Look for the item within the cache.
    return file_exists($this->resolve($key));

  }

  // Determine if an item  within the cache is expired.
  protected function expired( $item ) {

    // If the item doesn't have an expiration date, then assume it's not expired.
    if( !isset($item['expires']) ) return false;

    // Otherwise, determine if the item is expired based on its expiry time.
    return $item['expires'] < time();

  }

  // Refreshes the expiry time of some data within the cache.
  protected function refresh( $key, $expires = Cache::CACHE_EXPIRES_HOUR ) {

    // Verify that the key exists within the cache, then refresh its expiry time.
    if( $this->has($key) ) return $this->set($key, $this->get($key)['data'], $expires);

    // Otherwise, indicate that the expiry time could not be refreshed.
    return false;

  }

  // Save data to the cache.
  public function save( string $key, array $data ) {

    // Convert the data to a PHP string.
    $php = '<?php return '.var_export($data, true).'; ?>';

    // Get the cache file path.
    $path = DOMAIN.'/cache/'.ltrim(strtr($key, '.', '/'), '/').'.php';

    // Try to save the data to the cache.
    try {

      // Initialize a temporary cache file.
      $tmp = Cache::tmp($php, $path, 0777);

      // Overwrite the existing cache file, or initialize it.
      $tmp['move']($this->path.'/'.ltrim(strtr($key, '.', '/'), '/').'.php');

      // Return.
      return true;

    }

    // Otherwise, log an error.
    catch( Throwable $exception ) {

      // Log the error.
      error_log("Cache could not be updated: '$key'.");

      // Return.
      return false;

    }

  }

  // Reset the cache.
  protected function reset() {

    // Scan the contents of the cache.
    $keys = static::scan($this->path);

    // Wipe all items within the cache.
    foreach( $keys as $key ) { $this->unset($key); }

  }

  // Resolve the path within the cache.
  public function resolve( string $key = '' ) {

    // Resolve the path within the cache.
    return $this->path.'/'.ltrim($key, '/');

  }

  //*********** STATIC METHODS ***********//

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

  // Move a file from one place in the cache to another.
  public static function move( $from, $to, $overwrite = true, $mode = 0755, $recursive = true ) {

    // Verify that the item exists within the cache, and move it.
    if( Cache::exists($from) ) {

      // Create the directory if it doesn't exist.
      if( !Cache::isDirectory(dirname($to)) ) Cache::make(dirname($to), $mode, $recursive);

      // Prevent overwriting if the destination already exists and overwrite is disabled.
      if( !$overwrite and Cache::exists($to) ) return false;

      // Otherwise, move the item per usual.
      return rename($from, $to);

    }

    // Otherwise, indicate that nothing could be moved.
    return false;

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

  // Determines if a path is an existing symlink.
  public static function isSymlink( $path ) { return is_link(Cache::path($path)); }

  // Create a directory at a given path.
  public static function make( $path, $mode = 0755, $recursive = true ) {

    // Unset umask temporarily.
    $umask = umask(0);

    // Make directories using the given mode.
    $result = mkdir(Cache::path($path), $mode, $recursive);

    // Reset umask.
    umask($umask);

    // Return the result.
    return $result;

  }

  // Scans the contents of a cache directory.
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
  public static function tmp( $data, $path = null, $mode = 0755, $recursive = true ) {

    // Get the temporary cache path.
    $tmp = Cache::path(CONFIG['engine']['cache']['tmp']);

    // Ensure that the temporary cache directory exists.
    if( !Cache::isDirectory($tmp) ) Cache::make($tmp, $mode, $recursive);

    // Get the temporary file path.
    $path = isset($path) ? cleanpath($tmp.'/'.$path) : tempnam($tmp, 'eul_');

    // Write the data to the file.
    $result = Cache::write($path, $data);

    // Verify that the temporary file was created, or throw an exception.
    if( $result === false ) throw new Exception("Temporary file could not be created.");

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

      },
      'move' => function( $dest, $overwrite = true ) use ($path, $mode, $recursive) {

        // Move the file.
        return Cache::move($path, $dest, $overwrite, $mode, $recursive);

      }
    ];

  }

}

?>
