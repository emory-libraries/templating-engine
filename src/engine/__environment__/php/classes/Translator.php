<?php

/*
 * Translator
 *
 * Translates some data given in the form of an associative 
 * array to adhere to a given data model according to its
 * data type transformations.
 */
class Translator {
  
  // Defines recognized types and their respective translator methods.
  public static $translators = [
    'translateXML' => ['xml']
  ];
  
  // Translate some data based on a given data model.
  public static function translate( array $data, array $model, $type ) {
    
    // Determine the translator method to be used based on the data type.
    foreach( self::$translators as $method => $types ) {
      
      // Pass the file contents to the appropriate translator method.
      if( in_array($type, $types) ) return forward_static_call("Translator::{$method}", $data, $model);
      
    }
    
  }
  
  // Translate some XML data based on a given data model.
  public static function translateXML( array $data, array $model ) {
    
    // Initialize the result.
    $result = [];
    
    // Extract the XML meta data.
    foreach( array_flatten(array_get($model, 'meta', [])) as $key => $path ) {
      
      $result = array_set($result, $key, array_get($data, $path));
      
    }
    
    // Extract the XML base data.
    $data = array_get($data, array_get($model, 'data'), []);
      
    // Combine the base data and meta data.
    $result = array_merge($result, $data);
    
    // Initialize a helper for casting values to their appropriate data types.
    $cast = function($value, $key) use (&$cast) {
      
      // Do not cast any HTML content.
      if( in_array($key, CONFIG['config']['xml']['config']['html']) ) return $value;
      
      // Otherwise, cast the value to its corresponding data type.
      return Cast::cast($value);
      
    };
    
    // Cast all values to their appropriate types, except for HTML content.
    array_walk_recursive($result, $cast);
    
    // Return the result.
    return $result;
    
  }
  
}

?>
