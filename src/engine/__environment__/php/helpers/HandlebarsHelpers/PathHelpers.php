<?php

namespace HandlebarsHelpers;

trait PathHelpers {
  
  // Get an absolute path from the given path.
  public static function absolute( $path, $options = [] ) {
    
    // Get options.
    $options = func_num_args() == 2 ? $options : ['data' => []];
    
    // Get the context.
    $context = array_merge([], array_get($options['_this'], 'options', []), array_get($options, 'hash', []));
    
    // Merge named options into context if given.
    if( isset($options['name']) and isset($context[$options['name']]) ) $context = array_merge([], $context[$options['name']], $context);
    
    // Merge the root into the context.
    $context = array_merge([], array_get($options, 'data.root', []), $context);
    
    // Get the current working directory.
    $cwd = array_get($context, 'cwd', Path::toRootRelative('/'));
    
    // Resolve the path.
    return Path::resolve($cwd, $path);
    
  }
  
  // Get the relative path from path `a` to path `b`.
  public static function relative( $a, $b ) {
    
    // Throw an error if a string was not given for point A or point B.
    if( !is_string($a) ) throw new Error('Expected first path to be a string');
    if( !is_string($b) ) throw new Error('Expected second path to be a string');
    
    // Return the relative path between the two points.
    return Path::relative($a, $b);
    
  }
  
  // Get the dirname of the given path.
  public static function dirname( $path ) { return Path::dirname($path); }
  
  // Get the basename of the given path.
  public static function basename( $path ) { return Path::basename($path); }
  
  // Get the extname of the given path.
  public static function extname( $path ) { return Path::extname($path); }
  
  // Get the filename of the given path.
  public static function stem( $path ) { return Path::filename($path); }
  
  // Resolve an internal path from the given path.
  public static function resolve( $path, $options = [] ) {
    
    // Get options.
    $options = func_num_args() == 2 ? $options : ['data' => []];
    
    // Get the context.
    $context = array_merge([], array_get($options['_this'], 'options', []), array_get($options, 'hash', []));
    
    // Merge named options into context if given.
    if( isset($options['name']) and isset($context[$options['name']]) ) $context = array_merge([], $context[$options['name']], $context);
    
    // Merge the root into the context.
    $context = array_merge([], array_get($options, 'data.root', []), $context);
    
    // Get the current working directory.
    $cwd = array_get($context, 'cwd', Path::toRootRelative('/'));
    
    // Resolve the path.
    return Path::resolve($cwd, $path);
    
  }
  
  // Get specific (joined) segments of a file path by passing a range of path indices.
  public static function segments( $path, $a, $b, array $options ) {
    
    // Ensure that the path is a string, or throw an error otherwise.
    if( !is_string($path) ) throw new Error('Expected path to be a string');
    
    // Get the segments of the path.
    return Path::slice($path, $a, $b);
    
  }

}

?>