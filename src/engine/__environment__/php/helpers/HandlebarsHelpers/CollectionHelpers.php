<?php

namespace HandlebarsHelpers;

trait CollectionHelpers {
  
  // Inline and block helper that returns truthy if a given collection is empty.
  public static function isEmpty( array $collection, $options = [] ) {
    
    $arguments = func_get_args();
    $options = array_last($options);
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
    
    // Get a list of all helpers.
    $helpers = API::get('helpers/');
    
    // Iterate over a collection.
    if( is_associative_array($collection) ) return $helpers['forOwn']($collection, $options);
    
    // Otherwise, iterate over an array.
    else if( is_array($collection) ) return $helpers['forEach']($collection, $options);
    
    // Otherwise, render the inverse block.
    return $options['inverse']();
    
  }
  
}

?>