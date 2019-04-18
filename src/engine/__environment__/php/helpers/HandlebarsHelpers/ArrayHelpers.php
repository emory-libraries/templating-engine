<?php

namespace HandlebarsHelpers;

use __ as _;

trait ArrayHelpers {
  
  // Returns all of the items in an array after the specified index. [opposite of before]
  public static function after( array $array, $n ) {
    
    return (isset($array) ? _::slice($array, $n) : []);
    
  }
  
  // Return all of the items in an array before the specified index. [opposite of after]
  public static function before( array $array, $n ) {
    
    return (isset($array) ? _::slice($array, 0, $n) : []);
    
  }
  
  // Cast the given `value` to an array.
  public static function arrayify( $value ) {
    
    return (isset($value) ? (is_array($value) ? $value : [$value]) : []);
    
  }
  
  // Block helper that gets the item and index of each item within the array.
  public static function eachIndex( array $array, $options ) {
    
    $result = '';
    
    foreach( $array as $index => $item ) {
      
      $result .= $options['fn']([
        'item'  => $item,
        'index' => $index
      ]);
      
    }
    
    return $result;
    
  }
  
  // Block helper that filters the array and renders truthy values or the inverse block otherwise.
  public static function filter( array $array, $value, $options ) {
    
    $content = '';
    $results = [];
    $prop = array_get($options, 'hash.prop');
      
    // Filter on a specific property.
    if( isset($prop) ) { $results = _::filter($array, [$prop, $value]); }
    
    // Otherwise, filter on a string.
    else { $results = _::filter($array, function($item) use ($value) {
      
      return $item === $value;
      
    }); }

    // Render the block.
    if( count($results) > 0 ) {
      
      foreach( $results as $result ) { $content .= $options['fn']($result); }
      
      return $content;
      
    }
    
    // Otherwise, render the inverse block.
    return $options['inverse']();
    
  }
  
  // Returns the first item, or first `n` items of an array or string. [opposite of last]
  public static function first( $value, $n = 1 ) {
    
    // Set the default number of items.
    if( is_array($n) ) $n = 1;
    
    if( isset($value ) ) {
    
      if( is_array($value) ) return _::take($array, $n);
    
      if( is_string($value) ) return substr($value, 0, $n);
      
    }
    
    return null;
    
  }
  
  // Returns the last item, or last `n` items of an array or string. [opposite of first]
  public static function last( $value, $n = 1 ) {
    
    // Set the default number of items.
    if( is_array($n) or !is_numeric($n) ) $n = 1;
    
    if( isset($value) ) {
      
      if( is_array($value) ) return _::takeRight($array, $n);
    
      if( is_string($value) ) return substr($value, -1, $n);
      
    }
    
    return null;
    
  }
  
  // Iterates over each item in an array and exposes the current item's context.
  public static function forEach( array $array, $options ) {
    
    $data = array_merge([], array_get($options, 'data', []), array_get($options, 'hash', []));
    $buffer = '';
    $index = -1;
    $length = count($array);
    
    while( ++$index < $length ) {
      
      $item = $array[$index];
      
      $data['index'] = $index;
      $item['index'] = $index + 1;
      $item['total'] = $length;
      $item['isFirst'] = $index === 0;
      $item['isLast'] = $index === ($length - 1);
      
      $buffer .= $options['fn']($item, ['data' => $data]);
      
    }
    
    return $buffer;
    
  }
  
  // Block helper that renders the block if an array has the given `value`.
  public static function inArray( array $array, $value, $options ) {
    
    if( in_array($value, $array) ) return $options['fn']();
    
    return $options['inverse']();
    
  }
  
  // Returns true if `value` is a simple array (non-associative).
  public static function isArray( $value ) {
    
    return (is_array($value) and !is_associative_array($value));
    
  }
  
  // Returns the item from `array` at index `i`.
  public static function itemAt( array $array, $i = 0 ) {
    
    // Set the default index.
    if( is_array($i) ) $i = 0;
    
    return ($i < 0 ? array_get($array, count($array) + $i) : array_get($array, $i));
    
  }
  
  // Join all elements of array into a string, optionally using a given separator.
  public static function join( array $array, $separator = ', ' ) {
    
    // Set the default separator.
    if( is_array($separator) ) $separator = ', ';
    
    return implode($separator, $array);
    
  }
  
  // Returns true if the length of the given `value` is equal to the given `length`. [aliased as lengthEqual]
  public static function equalsLength( $value, $length, $options ) {
    
    // Get arguments and options.
    $arguments = func_get_args();
    $options = _::last($arguments);
    $length = func_num_args() == 2 ? 0 : $length;
    $count = 0; 
    
    // Get the length of the value.
    if( is_string($value) ) $count = strlen($value);
    if( is_array($value) ) $count = count($value);
    
    // Handle blocks.
    if( isset($options['fn']) ) {
      
      if( $count === $length ) return $options['fn']();
      
      return $options['inverse']();
      
    }
    
    // Otherwise, handle inlines.
    return $count === $length;
    
  }
  
  // Return true if the length of the given `value` is equal to the given `length`. [alias for equalsLength]
  public static function lengthEqual( $value, $length, $options ) {
    
    return forward_static_call('HandlebarsHelpers\ArrayHelpers::equalsLength', $value, $length, $options);
    
  }
  
  // Returns the length of the given string or array.
  public static function length( $value ) {
    
    if( is_array($value) ) {
      
      if( is_associative_array($value) ) return count(array_keys($value));
      
      return count($value);
      
    }
    
    if( is_string($value) ) return strlen($value);
    
    return 0;
    
  }
  
  // Returns a new array, created by calling `iteratee` on each element of the given `array`.
  public static function map( array $array, $iteratee ) {
  
    global $HELPERS;
    
    if( gettype($iteratee) == 'callable' ) return _::map($array, $iteratee);
    
    if( array_key_exists($iteratee, $HELPERS) ) return _::map($array, $HELPERS[$iteratee]);
    
    return $array;
    
  }
  
  // Create an array of values of the given `property` from the given object or collection.
  public static function pluck( array $array, $property ) {
    
    $result = [];
    
    if( is_associative_array($array) ) array_push($result, array_get($array, $property));
    
    else {
    
      foreach( $array as $item ) { array_push($result, array_get($item, $property)); }
      
    }
    
    return $result;
    
  }
  
  // Reverse the elements in an array or characters in a string.
  public static function reverse( $value ) {
    
    if( is_array($value) ) return array_reverse($value);
    
    if( is_string($value) ) return strrev($value);
    
    return $value;
    
  }
  
  // Block helper that checks if the callback returns truthy for some value in the array.
  public static function some( array $array, $iteratee, $options ) {
    
    global $HELPERS;
    
    if( gettype($iteratee) == 'callable' ) {
      
      if( _::some($array, $iteratee) ) return $options['fn']();
      
      return $options['inverse']();
      
    }
    
    if( array_key_exists($iteratee, $HELPERS) ) {
      
      if( _::some($array, $HELPERS[$iteratee]) ) return $options['fn']();
      
      return $options['inverse']();
      
    }
    
    return $options['inverse']();
    
  }
  
  // Sort the given `array`, optionally in reverse (descending) order.
  public static function sort( array $array, $options ) {
    
    $reverse = array_get($options, 'hash.reverse', false);
    
    if( $reverse ) rsort($array);
    
    else sort($array);
    
    return $array;
    
  }
  
  // Sort an `array` by `property`, or optionally passing a sorting function.
  public static function sortBy( array $array, $property, $options ) {
    
    global $HELPERS;
    
    $reverse = array_get($options, 'hash.reverse', false);
    
    if( gettype($property) == 'callable' ) {
      
      if( $reverse ) return array_reverse(_::sortBy($array, $property));
      
      return _::sortBy($array, $property);
      
    }
    
    if( array_key_exists($property, $HELPERS) ) {
      
      if( $reverse ) return array_reverse(_::sortBy($array, $HELPERS[$property]));
      
      return _::sortBy($array, $HELPERS[$property]);
      
    }
    
    if( $reverse ) return array_reverse(_::sortBy($array, [$property]));
    
    return _::sortBy($array, [$property]);
    
    
  }
  
  // Use the items in the array after the specific `index` as context inside a block. [opposite of withBefore]
  public static function withAfter( array $array, $index, $options ) {
    
    $array = _::slice($array, $index);
    $data = array_merge([], array_get($options, 'data', []));
    
    $result = '';
    
    foreach( $array as $item ) { $result .= $options['fn']($item, ['data' => $data]); }
      
    return $result;
    
  }
  
  // Use the items in the array before the specific `index` as context inside a block. [opposite of withAfter]
  public static function withBefore( array $array, $index, $options ) {
    
    $array = _::slice($array, 0, $index);
    $data = array_merge([], array_get($options, 'data', []));
    
    $result = '';
    
    foreach( $array as $item ) { $result .= $options['fn']($item, ['data' => $data]); }
      
    return $result;
    
  }
  
  // Use the first item in a collection inside a block context. [opposite of withLast]
  public static function withFirst( array $array, $index = 1, $options = [] ) {
    
    $arguments = func_get_args();
    $options = _::last($arguments);
    $index = func_num_args() == 2 ? 1 : _::last(_::initial($arguments));
    $data = array_merge([], array_get($options, 'data', []));
    
    $array = _::slice($array, 0, $index);
    $result = '';
    
    foreach( $array as $item ) { $result .= $options['fn']($item, ['data' => $data]); }
    
    return $result;
    
  }
  
  // Use the last item in a collection inside a block context. [opposite of withFirst]
  public static function withLast( array $array, $index = 1, $options = [] ) {
    
    $arguments = func_get_args();
    $options = _::last($arguments);
    $index = func_num_args() == 2 ? 1 : _::last(_::initial($arguments));
    $data = array_merge([], array_get($options, 'data', []));
    
    $array = _::slice($array, -$index);
    $result = '';
    
    foreach( $array as $item ) { $result .= $options['fn']($item, ['data' => $data]); }
    
    return $result;
    
  }
  
  // Block helper that groups array elements by given group `size`.
  public static function withGroup( array $array, $size, $options ) {
    
    $result = '';
    
    if( count($array) > 0 ) {
      
      $subcontext = [];
      
      $index = 0;
      
      foreach( $array as $item ) {
        
        if( $index > 0 and ($index % $size) === 0 ) {
        
          $result .= $options['fn']($subcontext);
        
          $subcontext = [];
          
        }
      
        array_push($subcontext, $item);
        
        $index++;
        
      }
      
      $result .= $options['fn']($subcontext);
      
    }
    
    return $result;
    
  }
  
  // Block helper that sorts a collection and exposes the sorted collection as the block context.
  public static function withSort( array $array, $property = null, $options = [] ) {
    
    $arguments = func_get_args();
    $options = _::last($arguments);
    $property = func_num_args() == 2 ? null : _::last(_::initial($arguments));
    $reverse = array_get($options, 'hash.reverse', false);
    
    $result = '';
    
    if( is_associative_array($array) ) return $result;
    
    if( isset($property) ) {
      
      $array = _::sortBy($array, [$property]);
      
      if( $reverse ) $array = array_reverse($array);
      
      foreach( $array as $item ) { $result .= $options['fn']($item); }
      
      return $result;
      
    }
    
    else {
      
      if( $reverse ) rsort($array);
      
      else sort($array);
      
      foreach( $array as $item ) { $result .= $options['fn']($item); }
      
      return $result;
      
    }
    
  }
  
  // Returns an array with all duplicate values removed.
  public static function unique( array $array ) {
    
    return array_unique($array);
    
  }
  
}

?>