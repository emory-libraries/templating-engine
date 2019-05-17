<?php

namespace HandlebarsHelpers;

trait StringHelpers {
  
  // TODO: Create definition for `append` helper.
  
  // TODO: Create definition for `prepend` helper.
  
  // TODO: Create definition for `camelcase` helper.
  
  // TODO: Create definition for `capitalize` helper.
  
  // TODO: Create definition for `center` helper.
  
  // TODO: Create definition for `chop` helper.
  
  // TODO: Create definition for `dashcase` helper.
  
  // TODO: Create definition for `dotcase` helper.
  
  // A block and inline helper that converts a string to lowercase. [aliased as downcase]
  public static function lowercase( $string ) {
    
    // Get options.
    $options = func_num_args() > 1 ? array_last(func_get_args()) : $string;
    $string = func_num_args() > 1 ? $string : '';
    
    // Lowercase the string or block.
    return (isset($options['fn']) ? strtolower($options['fn']()) : strtolower($string));
    
  }
  
  // A block and inline helper that converts a string to lowercase. [alias for lowercase]
  public static function downcase( $string ) {
    
    return forward_static_call('HandlebarsHelpers\StringHelpers::lowercase', $string);
    
  }
  
  // TODO: Create definition for `ellipsis` helper.
  
  // TODO: Create definition for `truncate` helper.
  
  // TODO: Create definition for `truncateWords` helper.
  
  // TODO: Create definition for `hyphenate` helper.
  
  // TODO: Create definition for `isString` helper.
  
  // TODO: Create definition for `occurrences` helper.
  
  // TODO: Create definition for `pascalcase` helper.
  
  // TODO: Create definition for `pathcase` helper.
  
  // TODO: Create definition for `plusify` helper.
  
  // TODO: Create definition for `raw` helper.
  
  // TODO: Create definition for `remove` helper.
  
  // TODO: Create definition for `removeFirst` helper.
  
  // TODO: Create definition for `replace` helper.
  
  // TODO: Create definition for `replaceFirst` helper.
  
  // TODO: Create definition for `reverse` helper.
  
  // TODO: Create definition for `sentence` helper.
  
  // TODO: Create definition for `split` helper.
  
  // TODO: Create definition for `startsWith` helper.
  
  // TODO: Create definition for `titleize` helper.
  
  // TODO: Create definition for `trim` helper.
  
  // TODO: Create definition for `trimLeft` helper.
  
  // TODO: Create definition for `trimRight` helper.
  
  // A block and inline helper that converts a string to uppercase. [aliased as upcase]
  public static function uppercase( $string ) {
    
    // Get options.
    $options = func_num_args() > 1 ? array_last(func_get_args()) : $string;
    $string = func_num_args() > 1 ? $string : '';
    
    // Lowercase the string or block.
    return (isset($options['fn']) ? strtoupper($options['fn']()) : strtoupper($string));
    
  }
  
  // A block and inline helper that converts a string to uppercase. [alias for uppercase]
  public static function upcase( $string ) {
    
    return forward_static_call('HandlebarsHelpers\StringHelpers::uppercase', $string);
    
  }
  
}

?>