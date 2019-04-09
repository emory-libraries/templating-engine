<?php

namespace HandlebarsHelpers;

use _;

trait CollectionHelpers {
  
  // Inline and block helper that returns truthy if a given collection is empty.
  public static function isEmpty( array $collection, $options = [] ) {
    
    $arguments = func_get_args();
    $options = _::last($options);
    $collection = func_num_args() == 2 ? $collection : false;
    
    if( $collection ) {
      
      if( is_associative_array($collection) ) {
        
        $keys = array_keys($collection);
        
        if( count($keys) === 0 ) return isset($options['fn']) ? $options['fn']() : true;
        
        return isset($options['inverse']) ? $options['inverse']() : false;
        
      } 
      
      else {
        
        if( count($collection) === 0 ) return isset($options['fn']) ? $options['fn']() : true;
        
        return isset($options['inverse']) ? $options['inverse']() : false;
        
      }
      
    }
    
    return isset($options['fn']) ? $options['fn']() : true;
    
  }
  
  // Block helper that iterates over a collection, using `forEach` or `forOwn`.
  public static function iterate( $collection, $options ) {
    
    // Use global helpers.
    global $HELPERS;
    
    // Iterate over a collection.
    if( is_associative_array($collection) ) return $HELPERS['forOwn']($collection, $options);
    
    // Otherwise, iterate over an array.
    else if( is_array($collection) ) return $HELPERS['forEach']($collection, $options);
    
    // Otherwise, render the inverse block.
    return $options['inverse']();
    
  }
  
}

?>