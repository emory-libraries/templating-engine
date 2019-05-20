<?php

namespace HandlebarsHelpers;

trait RegexHelpers {
  
  // Convert the given string to a regular expression.
  // FIXME: This throws an error when parentheses are used. This is a known issue with `LightnCandy`. See [#281](https://github.com/zordius/lightncandy/issues/281).
  public static function toRegex( $string, $locals = [], $options = [] ) {
    
    // Get locals and options.
    $options = func_num_args() == 3 ? $options : $locals;
    $locals = func_num_args() == 3 ? $locals : [];
    
    // Get the context.
    $context = array_merge([], array_get($options['_this'], 'options', []), $locals, array_get($options, 'hash', []));
    
    // Merge named options into context if given.
    if( isset($options['name']) and isset($context[$options['name']]) ) $context = array_merge([], $context[$options['name']], $context);
    
    // Get the regular expression flags.
    $flags = array_get($context, 'flags', '');
    
    // Convert the string to a regular expression.
    return "/$string/$flags";
    
  }
  
  // Returns truthy if the given `string` matches the given regex.
  public static function test( $string, $regex ) {
    
    // For non-strings, immediately fail.
    if( !is_string($string) ) return false;
    
    // Validate the regex and throw and error if invalid.
    if( @preg_match($regex, null) === false ) throw new Error('Expected a regular expression');
    
    // Test that the string matches the regex.
    return (preg_match($regex, $string) === 1);
    
  }
  
}

?>