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

  // Get a file's source path within the server.
  public static function source( $path, $base = [
    CONFIG['data']['site']['root'],
    CONFIG['data']['environment']['root'],
    CONFIG['patterns']['root'],
    CONFIG['engine']['meta']
  ] ) {

    // Get the file's directory path.
    $directory = dirname($path);

    // Remove data and patterns paths from the directory.
    foreach( $base as $remove ) { $directory = str_replace($remove, '', $directory); }

    // Get the dirname and basename.
    return (isset($directory) ? "$directory/" : "").basename($path);

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

  // Determine if a path or any/every path in an array of paths exists.
  public static function exists( $path, $every = false ) {

    // Determine if the path or array of paths exists.
    return (is_array($path) ? ($every ? array_every($path, 'file_exists') : array_some($path, 'file_exists')) : file_exists($path));

  }

  // Determine if a file path or any/every file path in an array of paths exists.
  public static function isFile( $path, $every = false ) {

    // Determine if the file path or array of file paths exists.
    return (is_array($path) ? ($every ? array_every($path, function($file) {

      // Check to see if the file path points to an actual file.
      return is_file($file);

    }) : array_some($path, function($file) {

      // Check to see if the file path points to an actual file.
      return is_file($file);

    })) : is_file($path));

  }

  // Determine if a directory path or any/every directory path in an array of paths exists.
  public static function isDirectory( $path, $every = false ) {

    // Determine if the directory path or array of directory paths exists.
    return (is_array($path) ? ($every ? array_every($path, function($directory) {

      // Check to see if the directory path points to an actual directory.
      return is_dir($directory);

    }) : array_some($path, function($directory) {

      // Check to see if the directory path points to an actual directory.
      return is_dir($directory);

    })) : is_dir($path));

  }

  // Determine if a symlink path or any/every symlink path in an array of paths exists.
  public static function isSymlink( $path, $every = false ) {

    // Determine if the directory path or array of directory paths exists.
    return (is_array($path) ? ($every ? array_every($path, function($directory) {

      // Check to see if the directory path points to an actual directory.
      return is_link($directory);

    }) : array_some($path, function($directory) {

      // Check to see if the directory path points to an actual directory.
      return is_link($directory);

    })) : is_link($path));

  }

  // Get the last modified time of a file.
  public static function modified( $path ) {

    // Clear any cached file states.
    clearstatcache();

    // Return the file's last modified time.
    return filemtime($path);

  }

  // Get metadata for a file or array of files.
  public static function metadata( $path, $recursive = true ) {

    // Clear any cached file stats.
    clearstatcache();

    // Create a helper method for getting path metadata.
    $metadata = function($path) {

      return [
        'id' => self::id($path),
        'path' => $path,
        'basename' => basename($path),
        'dirname' => dirname($path),
        'extension' => pathinfo($path, PATHINFO_EXTENSION),
        'modified' => @filemtime($path) ? (new DateTime)->setTimestamp(filemtime($path)) : false,
        'accessed' => @fileatime($path) ? (new DateTime)->setTimestamp(fileatime($path)) : false,
        'created' => @filectime($path) ? (new DateTime)->setTimestamp(filectime($path)) : false,
        'isFile' => is_file($path),
        'isDirectory' => is_dir($path),
        'isSymlink' => is_link($path),
        'isReadable' => is_readable($path),
        'isWritable' => is_writable($path),
        'size' => @filesize($path) ? filesize($path) : false,
        'owner' => @fileowner($path) ? fileowner($path) : false,
        'group' => @filegroup($path) ? filegroup($path) : false,
        'permissions' => @fileperms($path) ? fileperms($path) : false
      ];

    };

    // Get metadata for a single file.
    if( is_string($path) ) return $metadata($path);

    // Otherwise, read an array of files.
    else if( is_array($path) ) {

      // Traverse the file list.
      foreach( $path as $index => $file ) {

        // Recursively read all files within nested arrays if the recursive flag is set.
        if( is_array($file) and $recursive ) $path[$index] = File::metadata($file, $recursive);

        // Otherwise, read the file from its path.
        else $path[$index] = $metadata($file);

      }

      // Return the list of files.
      return $path;

    }

  }

}

?>
