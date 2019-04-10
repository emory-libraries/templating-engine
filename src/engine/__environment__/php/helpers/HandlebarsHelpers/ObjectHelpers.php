<?php

namespace HandlebarsHelpers;

use _;

trait ObjectHelpers {
  
  // TODO: Create definition for `extend` helper.
  
  // Block helper that iterates over the properties of an object. [aliased as forOwn]
  public static function forIn( array $object, $options ) {
    
    // Get data.
    $data = array_merge([], array_get($options, 'data', []), array_get($options, 'hash', []));
    
    // Initialize result.
    $result = '';
    
    // Render the block.
    foreach( $object as $key => $value ) {
      
      // Save the key.
      $data['key'] = $key;
      
      // Render the block.
      $render .= $options['fn']($value, ['data' => $date]);
      
    }
    
    // Return the result.
    return $result;
    
  }
  
  // Block helper that iterates over the own properties of an object. [alias for forIn]
  public static function forOwn( array $object, $options ) {
    
    return forward_static_call('HandlebarsHelpers\ObjectHelpers::forIn', $object, $options);
    
  }
  
  // TODO: Create definition for `toPath` helper.
  
  // Inline and block helper to get a value from the context using dot-delimited notation.
  public static function get( $property, array $context, $options ) {
    
    // Get the value.
    $value = array_get($context, $property);
  
    // Render the block if given.
    if( isset($options['fn']) ) return (isset($value) ? $options['fn']($value) : $options['inverse']($context));
    
    // Otherwise, return the value.
    return $value;
    
  }
  
  // TODO: Create definition for `getObject` helper.
  
  // TODO: Create definition for `hasOwn` helper.
  
  // TODO: Create definition for `isObject` helper.
  
  // Parses the given JSON string. [aliased as parseJSON]
  public static function JSONparse( $string ) {
    
    return json_decode($str, true);
    
  }
  
  // Parses the given JSON string. [alias for JSONparse]
  public static function parseJSON( $string ) {
    
    return forward_static_call('HandlebarsHelpers\ObjectHelpers::JSONparse', $string);
    
  }
  
  // Stringify an object as JSON. [aliased as stringifyJSON]
  public static function JSONstringify( array $object, $indent = false ) {
    
    // Set the default indentation.
    if( is_array($indent) ) $indent = false;
    
    // Stringify to JSON.
    return ((bool) $indent ? json_encode($object, JSON_PRETTY_PRINT) : json_encode($object));
    
  }
  
  // Stringify an object as JSON. [alias for JSONstringify]
  public static function stringifyJSON( array $object, $indent = false ) {
    
    return forward_static_call('HandlebarsHelpers\ObjectHelpers::JSONstringify', $object, $indent);
    
  }
  
  // TODO: Create definition for `merge` helper.
  
  // TODO: Create definition for `pick` helper.
  
}

?>