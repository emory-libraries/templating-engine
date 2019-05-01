<?php

/*
 * Data
 *
 * Reads and parses a data file given a file path.
 * Additionally, the static methods provided can be
 * used to read and parse various types of data
 * for the environment and/or site.
 */
class Data {
  
  // The path of the data file.
  public $path;
  
  // The ID of the data file.
  public $id;
  
  // The contents of the data file.
  public $data = [];
  
  // Defines flags that can be used for the merge method.
  const MERGE_PATHS = 1;
  const MERGE_CONTENTS = 2;
  const MERGE_NORMAL = 4;
  const MERGE_RECURSIVE = 8;
  const MERGE_KEYED = 16;
  const MERGE_GROUPED = 32;
  const MERGE_OVERRIDE = 64;
  
  // Constructs the data.
  function __construct( $path ) {
    
    // Extract the data from a valid path with given a string.
    if( is_string($path) ) {
    
      // Save the data file path.
      $this->path = $path;

      // Get the data file's ID.
      $this->id = File::id($path);

      // Get the data file's data.
      $this->data = self::parse($path);
      
    }
    
    // Otherwise, capture the data when given an array.
    else if( is_array($path) ) {
      
      // Capture the data from the given array.
      $this->path = array_get($path, 'path');
      $this->id = array_get($path, 'id');
      $this->data = array_get($path, 'data', []);
      
    }
    
  }
  
  // Read and parse a single data file or array of data files.
  public static function parse( $path, $recursive = true ) {
    
    // Immediately fail for invalid paths.
    if( !is_string($path) and !is_array($path) ) return false;
    
    // Define a helper method for handling file reading and parsing.
    $parse = function($path) use ($recursive) {
      
      // Parse all files within a directory.
      if( File::isDirectory($path) ) {
        
        // Locate files within the directory.
        $files = $recursive ? scandir_recursive($path, $path) : array_map(function($file) use ($path) {
          
          // Convert the file path to an absolute path.
          return "$file/$path";
          
        }, scandir_clean($path));
        
        // Parse all files within the directory.
        $files = array_reduce($files, function($result, $file) {
          
          // Parse the data file.
          $result[$file] = self::parse($file);
          
          // Continue reducing.
          return $result;
          
        }, []);
        
        // Return the files.
        return $files;
        
      }
    
      // Ensure that the path points to an actual file.
      else if( File::isFile($path) ) {

        // Get the data file's extension.
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        // Get the set of extensions that are valid for a data file.
        $exts = array_merge(...array_values(Transformer::$transformers));

        // Ensure that the path points specifically to a data file.
        if( !in_array($ext, $exts) ) return false;

        // Get the contents of the data file.
        $data = File::read($path);

        // Transform the data.
        $data = Transformer::transform($data, $ext);

        // Return the transformed contents of the data file.
        return $data;
        
      }
      
      // Return false all other invalid data.
      return false;
      
    };
    
    // Parse an single file.
    if( is_string($path) ) return $parse($path);
    
    // Otherwise, parse an array of files.
    return array_reduce($path, function($result, $file) use ($parse) {
      
      // Merge directories into the result.
      if( File::isDirectory($file) ) return array_merge($result, $parse($file));
      
      // Otherwise, add files to the array, where the file path is the key and contents is the value.
      $result[$file] = $parse($file);
      
      // Continue reducing.
      return $result;
      
    }, []);
    
  }
  
  // Read, parse, and merge one or more data files.
  public static function merge( $path, $flags = self::MERGE_KEYED | SELF::MERGE_RECURSIVE ) {
    
    // Immediately fail for invalid paths.
    if( !is_string($path) and !is_array($path) ) return false;
    
    // Convert string paths to an array.
    $path = is_array($path) ? $path : [$path];
    
    // Parse data files for all paths in the array.
    $path = self::parse($path);
    
    // Filter out any invalid data file paths.
    $path = array_filter($path, function($data) {
      
      // Ignore invalid data.
      return $data !== false;
      
    });

    // Merge the data on keys, where keys are composed of to data file IDs.
    if( $flags & self::MERGE_KEYED ) {
      
      // Initialize the result.
      $result = [];
      
      // Merge shared data by key.
      foreach( $path as $file => $content ) {
        
        // Derive the key from the file's ID.
        $key = File::id($file);
        
        // Get the existing data for that key.
        $data = array_get($result, $key, []);
        
        // Group the data by key.
        if( $flags & self::MERGE_GROUPED ) {
          
          // Add the data into the group.
          $result = array_set($result, $key, array_merge([], $data, [$content]));
          
        }
          
        
        // Recursively merge the data into the keyed data.
        else if( $flags & self::MERGE_RECURSIVE ) {
          
          // Recursively merge the data.
          $result = array_set($result, $key, array_merge_recursive($data, $content));
          
        }
        
        // Otherwise, merge the data into the keyed data.
        else if( $flags & self::MERGE_NORMAL ) {
          
          // Merge that data normally.
          $result = array_set($result, $key, array_merge($data, $content));
          
        }
        
        // Otherwise, set and/or override keyed data.
        else $result = array_set($result, $key, $content, ($flags & self::MERGE_OVERRIDE));
        
      }
      
      // Return the result.
      return $result;
      
    }
  
    // Recursively merge the data, and return it. 
    if( $flags & self::MERGE_RECURSIVE ) return array_merge_recursive(...array_values($path));
    
    // Otherwise, merge the data, and return it.
    if( $flags & self::MERGE_NORMAL ) return array_merge(...array_values($path));
    
    // Otherwise, return only the data contents.
    if( $flags & self::MERGE_CONTENTS ) return array_values($path);
    
    // Otherwise, return the data as is with paths included.
    return $path;
    
  }
  
  // Compile the meta data set for a request.
  public static function compile( Data $data, Request $request, Route $route ) {

    // Get global, meta, and shared data.
    $global = Data::merge($route->global, Data::MERGE_KEYED | Data::MERGE_RECURSIVE);
    $meta = Data::merge($route->meta, Data::MERGE_KEYED | Data::MERGE_RECURSIVE);
    $shared = Data::merge($route->shared, Data::MERGE_KEYED | Data::MERGE_GROUPED);
    
    // Get parameter data that was passed along with the request.
    $params = $request->params;

    // Merge additional data into the route's endpoint data.
    $data->data = array_merge($data->data, [
      '__global__' => $global,
      '__meta__' => $meta,
      '__shared__' => $shared,
      '__params__' => $params
    ]);
    
    // Return the compiled data.
    return $data;
    
  }
  
}

?>