<?php

namespace HandlebarsHelpers;

trait MiscHelpers {
  
  // Block helper for exposing private `@` variables on the context.
  public static function frame( $context, $options = [] ) {
    
    // Get the options and context.
    $options = func_num_args() == 2 ? $options : $context;
    $context = func_num_args() == 2 ? $context : array_get($options, 'data', []);
    
    // Initialize a frame.
    $frame = array_merge([], $context, ['_parent' => $context], $options['hash']);
    
    // Render the block with the frame.
    return $options['fn']($options['_this'], ['data' => $frame]);
    
  }
  
  // Return the given value of a `property` from the context's options.
  public static function option( $property, $locals = [], $options = [] ) {
    
    // Get locals and options.
    $options = func_num_args() == 3 ? $options : $locals;
    $locals = func_num_args() == 3 ? $locals : [];
    
    // Get the options context.
    $context = array_merge([], $locals, $options['hash']);
    $context = array_merge([], array_get($options['_this'], 'options', []), $context);
    
    // Get named options if given.
    if( isset($options['name']) and isset($context[$options['name']]) ) {
      
      // Merge named options into the context.
      $context = array_merge([], $context[$options['name']], $context);
      
    }
    
    // Get the property from the context.
    return array_get($context, $property);
    
  }
  
  // Block helper to render a block without taking any arguments.
  public static function noop( $options ) {
    
    return $options['fn']();
    
  }
  
  // Get the native type of the given value.
  public static function typeOf( $value ) { return gettype($value); }
  
  // Block helper that builds the context for the block from the options hash.
  public static function withHash( $options ) {
    
    // If a hash was given, then render the block with the hash.
    if( isset($options['hash']) and !empty($options['hash']) ) return $options['fn']($options['hash']);
    
    // Otherwise, render the inverse block.
    return $options['inverse']();
    
  }
  
}

?>