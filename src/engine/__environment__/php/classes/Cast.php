<?php

use __ as _;
use Moment\Moment;

/*
 * Cast
 *
 * Casts values to their native data types.
 */
class Cast {
  
  // Defines the values recognized as being truthy.
  public static $truthy = [
    'true',
    'positive',
    'yes',
    'aye',
    'ok',
    'yep',
    'yup',
    'yeah',
    'yah',
    'ya',
    'on',
    'enabled'
  ];
  
  // Defines the values recognized as being falsey.
  public static $falsey = [
    'false',
    'negative',
    'no',
    'none',
    'nope',
    'nah',
    'nay',
    'nul',
    'nyet',
    'nix',
    'nada',
    'off',
    'disabled'
  ];
  
  // Defines the values recognized as being null.
  public static $null = [
    'null',
    'nil',
    'nul',
    'undefined',
    ''
  ];
  
  // Defines the regexes used to identify some types.
  public static $regex = [
    
    'array' => '/^((?:\S|\ )+?,)+?(\S|\ )+?$/',
    'list' => '/^([^.?!]+?[,;] ?)+([^.?!]+)$/'
    
  ];
  
  // Cast values to their native data types.
  public static function cast( $value, $deep = true ) {
    
    // Cast an array of values.
    if( is_array($value) ) return forward_static_call('Cast::castArray', $value, $deep);
    
    // Get the type of the value.
    $type = self::type($value);
    
    // Cast the value to its data type.
    return forward_static_call('Cast::to'.ucfirst($type), $value);
    
  }
  
  // Cast an array of values to their native data types.
  public static function castArray( array $values, $deep = true ) {
    
    // Get the types of each item in the array.
    $types = self::typeArray($values, $deep);
    
    // Cast each item in the array to its data type.
    foreach( $types as $key => $type ) {
      
      // Cast non-array items.
      if( !is_array($type) ) $values[$key] = forward_static_call('Cast::to'.ucfirst($type), $values[$key]);
      
      // Otherwise, recursively cast items within nested arrays if deep mode is enabled.
      else if( $deep ) $values[$key] = self::castArray($values[$key]);
      
    }
    
    // Return array with casted values.
    return $values;
    
  }
  
  // Determine the data type to cast to for the given value.
  public static function type( $value ) {
    
    // Determine the data type for an array of values.
    if( is_array($value) ) return forward_static_call('Cast::typeArray', $value);
    
    // Check for array types.
    else if( self::isArray($value) ) return 'array';
    
    // Check for boolean types.
    else if( self::isBool($value) ) {
      if( self::isTruthy($value) ) return 'truthy';
      if( self::isFalsey($value) ) return 'falsey';
      return 'bool';
    }
    
    // Check for list types.
    else if( self::isList($value) ) return 'list';
    
    // Check for null types.
    else if( self::isNull($value) ) return 'null';
    
    // Check for numeric types.
    else if( self::isNumeric($value) ) {
      if( self::isInt($value) ) return 'int';
      if( self::isFloat($value) ) return 'float';
      return 'numeric';
    }
    
    // Check for date types.
    else if( self::isDate($value) ) return 'date';
    
    // Otherwise, assume the value is a string.
    return 'string';
    
  }
  
  // Determine the data types to be cast to for all items within the given array.
  public static function typeArray( array $values, $deep = true ) {
    
    // Determine the data type for each item within the array.
    foreach( $values as $key => $value ) {
      
      // Get the type of non-array values.
      if( !is_array($value) ) $values[$key] = self::type($value);
      
      // Otherwise, recursively get data types for nested arrays if deep mode is enabled.
      else if( $deep ) $values[$key] = self::typeArray($value, true);
      
    }
    
    // Return array of types.
    return $values;
    
  }
  
  // Check if a value is of type `string`.
  public static function isString( $value ) {
    
    // Determine if the value is numeric.
    return is_string($value);
    
  }
  
  // Check if a value is of type `numeric`.
  public static function isNumeric( $value ) {
    
    // Determine if the value is numeric.
    return is_numeric($value);
    
  }
  
  // Check if a value is of type `int`.
  public static function isInt( $value ) {
    
    // Determine if the value is an integer.
    return (self::isNumeric($value) and strpos((string) $value, '.') === false);
    
  }
  
  // Check if a value is of type `int`. [alias]
  public static function isInteger( $value ) {
    
    // Determine if the value is an integer.
    return forward_static_call('Cast::isInt', $value);
    
  }
  
  // Check if a value is of type `float`.
  public static function isFloat( $value ) {
    
    // Determine if the value is an integer.
    return (self::isNumeric($value) and strpos((string) $value, '.') !== false);
    
  }
  
  // Checks if a value is of type `float`. [alias]
  public static function isDouble( $value ) {
    
    // Determine if the value is a float.
    return forward_static_call('Cast::isFloat', $value);
    
  }
  
  // Check if a value is of type `date`.
  public static function isDate( $value ) {
    
    // Initialize the result.
    $result = false;
    
    // Initialize the moment flag.
    $moment = false;
    
    // Try to determine if the value is a date using moment.
    try {
      
      // Try to instantiate a moment.
      new Moment($value);
      
      // If it works, set the moment flag.
      $moment = true;
      
    } catch( Exception $e ) {
    
      // If it fails, make sure the moment flag is set to false.
      $moment = false;
      
    }
    
    // Determine if the value is a date using moment.
    if( $moment ) $result = true;
    
    // Otherwise, determine if the value is a date using datetime.
    else if( strtotime((string) $value) !== false ) $result = true;
    
    // Otherwise, try to determine if the value is a date using our date class.
    else if( Date::parse((string) $value) !== false ) $result = true;
    
    // Return the result.
    return $result;
    
  }
  
  // Check if a value is of type `array`.
  public static function isArray( $value ) {
    
    // Determine if the value is an array.
    return preg_match(self::$regex['array'], (string) $value);
    
  }
  
  // Check if a value is of type `list`.
  public static function isList( $value ) {
    
    // Determine if the value is a list.
    return preg_match(self::$regex['list'], (string) $value);
    
  }
  
  // Check if a value is of type `bool`.
  public static function isBool( $value ) {
    
    // Force the string to be lowercase.
    $string = (string) strtolower($value);
    
    // Determine if the value is a boolean.
    return ((in_array($string, self::$truthy) or $value === true) or (in_array($string, self::$falsey) or $value === false));
    
  }
  
  // Check if a value is of type `bool`. [alias]
  public static function isBoolean( $value ) {
    
    // Determine if the value is a boolean.
    return forward_static_call('Cast::isBool', $value);
    
  }
  
  // Check if a value is of type `truthy`.
  public static function isTruthy( $value ) {
    
    // Determine if the value is truthy.
    return in_array((string) $value, self::$truthy);
    
  }
  
  // Check if a value is of type `falsey`.
  public static function isFalsey( $value ) {
    
    // Determine if the value is falsey.
    return in_array((string) $value, self::$falsey);
    
  }
  
  // Check if a value is of type `null`.
  public static function isNull( $value ) {
    
    // Determine if the value is null.
    return (in_array((string) $value, self::$null) or is_null($value));
    
  }
  
  // Cast a value to type `int`.
  public static function toInt( $value ) { return (int) $value; }
  
  // Cast a value to type `int`. [alias]
  public static function toInteger( $value ) { return forward_static_call('Cast::toInt', $value); }
  
  // Cast a value to type `float`.
  public static function toFloat( $value ) { return (float) $value; }
  
  // Cast a value to numeric.
  public static function toNumeric( $value ) { 
    
    // Return integer values.
    if( self::isInt($value) ) return forward_static_call('Cast::toInt', $value);
    
    // Return float values.
    if( self::isFloat($value) ) return forward_static_call('Cast::toFloat', $value);
  
    // Otherwise, return a float by default just to be safe.
    return (float) $value;
  
  }
  
  // Cast a value to type `float`. [alias]
  public static function toDouble( $value ) { return forward_static_call('Cast::toFloat', $value); }
  
  // Cast a value to type `date`.
  public static function toDate( $value ) {
   
    // Initialize the result.
    $result = $value;
    
    // Initialize the moment flag.
    $moment = false;
    
    // Try to cast the value to a date using moment.
    try {
      
      // Try to instantiate a moment.
      new Moment($value);
      
      // If it works, set the moment flag.
      $moment = true;
      
    } catch( Exception $e ) {
    
      // If it fails, make sure the moment flag is set to false.
      $moment = false;
      
    }
    
    // Determine if the value is a date using moment.
    if( $moment ) $result = new Moment((string) $value);
    
    // Otherwise, determine if the value is a date using datetime.
    else if( strtotime((string) $value) !== false ) $result = new Moment(strtotime((string) $value));
    
    // Otherwise, try to determine if the value is a date using our date class.
    else if( Date::parse((string) $value) !== false ) $result = Moment::fromDateTime(Date::parse((string) $value)['datetime']);
    
    // Return the result.
    return $result;
    
  }
  
  // Cast a value to type `array`.
  public static function toArray( $value ) {
    
    // Strip array wrappers and whitespace from the value.
    $value = _::trim($value, ' [](){}');
    
    // Convert to an array, and clean up all array items.
    $array = array_map('trim', explode(',', $value));
    
    // Convert simple arrays to associative arrays whenever possible.
    foreach( $array as $key => $value ) {
      
      // Attempt to extract any key-value pairs.
      $pair = explode(':', $value);
      
      // Convert key-value pairs to associative array entries.
      if( count($pair) == 2 ) {
        
        // Save the key-value pair.
        $array[_::trim($pair[0], ' "\'')] = _::trim($pair[1], ' "\'');
        
        // Then, unset its previous key.
        unset($array[$key]);
        
      }
      
      // Attempt to cast each array value.
      else $array[$key] = self::cast($value);
      
    }
    
    // Return.
    return $array;
    
  }
  
  // Cast a value to type `list`. [alias]
  public static function toList( $value ) { return forward_static_call('Cast::toArray', $value); }
  
  // Cast a value to type `bool`.
  public static function toBool( $value ) { 
    
    // Return truthy values.
    if( self::isTruthy($value) ) return forward_static_call('Cast::toTruthy', $value);
    
    // Return falsey values.
    if( self::isFalsey($value) ) return forward_static_call('Cast::toFalsey', $value);
    
    // Otherwise, literally convert the value to boolean.
    return (bool) $value;
  
  }
  
  // Cast a value to type `bool`. [alias]
  public static function toBoolean( $value ) { return forward_static_call('Cast::toBoolean', $value); }
  
  // Cast a value to type `truthy`.
  public static function toTruthy( $value ) { return true; }
  
  // Cast a value to type `falsey`.
  public static function toFalsey( $value ) { return false; }
  
  // Cast a value to type `null`.
  public static function toNull( $value ) { return null; }
  
  // Cast a value to type `string`.
  public static function toString( $value ) { return (string) $value; }
  
  // This does nothing but is needed in order to prevent PHP from whining.
  function __construct() {}
  
}

?>