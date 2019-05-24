<?php

namespace CustomHelpers;

trait ObjectHelpers { 
  
  // Get object keys.
  public static function keys( array $object ) { return array_keys($object); }
  
  // Get object values.
  public static function values( array $object ) { return array_values($object); }
  
  // Convert sets of key-value pairs to objects.
  public static function objectify( array $options ) {
    
    // Create an object from the options hash.
    return array_get($options, 'hash', []);
    
  }
  
}

?>