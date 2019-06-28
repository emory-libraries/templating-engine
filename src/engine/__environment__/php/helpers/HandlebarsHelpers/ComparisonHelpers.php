<?php

namespace HandlebarsHelpers;

trait ComparisonHelpers {

  // Inline and block helper that returns truthy if all conditionals are truthy.
  public static function and( ...$conditions ) {

    // Extract options.
    $options = array_last($conditions);
    $conditions = array_head($conditions);
    $result = true;

    // Ensure that all conditions are truthy.
    foreach( $conditions as $condition ) {

      if( !$condition ) {

        $result = false;

        break;

      }

    }

    // Return truthy.
   if( $result ) return (isset($options['fn']) ? $options['fn'] : true);

    // Return falsey.
    return (isset($options['inverse']) ? $options['inverse']() : false);

  }

  // Inline and block helper that returns truthy if any of the given values are truthy.
  public static function or( ...$conditions ) {

    // Extract options.
    $options = array_last($conditions);
    $conditions = array_head($conditions);
    $result = false;

    // Ensure that at least one conditions is truthy.
    foreach( $conditions as $condition ) {

      if( $condition ) {

        $result = true;

        break;

      }

    }

    // Return truthy.
   if( $result ) return (isset($options['fn']) ? $options['fn'] : true);

    // Return falsey.
    return (isset($options['inverse']) ? $options['inverse']() : false);

  }

  // Inline and block helper that returns truthy if the `value` is falsey.
  public static function not( $value, $options ) {

    $result = !$value;

    if( $result ) return (isset($options['fn']) ? $options['fn']() : true);

    return (isset($options['inverse']) ? $options['inverse']() : false);

  }

  // Inline and block helper that returns truthy if the values are comparable.
  public static function compare( $a, $operator, $b, $options ) {

    $arguments = func_get_args();
    $options = array_last($arguments);
    $b = func_num_args() < 4 ? $operator : $b;
    $operator = func_num_args() < 4 ? '==' : $operator;

    // Initialize result.
    $result;

    // Compare values.
    switch ($operator) {
      case '==':
        $result = ($a == $b);
        break;
      case '===':
        $result = ($a === $b);
        break;
      case '!=':
        $result = ($a != $b);
        break;
      case '!==':
        $result = ($a !== $b);
        break;
      case '<':
        $result = ($a < $b);
        break;
      case '>':
        $result = ($a > $b);
        break;
      case '<=':
        $result = ($a <= $b);
        break;
      case '>=':
        $result = ($a >= $b);
        break;
      case 'typeof':
        $result = (gettype($a) === $b);
        break;
    }

    // Return truthy.
    if( $result ) return (isset($options['fn']) ? $options['fn']() : true);

    // Otherwise, return falsey.
    return (isset($options['inverse']) ? $options['inverse']() : false);

  }

  // Inline and block helper that returns truthy if `collection` has a given `value`.
  public static function contains( $collection, $value, $index = 0, $options = [] ) {

    // Get result.
    $arguments = func_get_args();
    $options = array_last($arguments);
    $index = func_num_args() > 3 ? $index : 0;
    $result = in_array($value, array_slice($collection, $index));

    // Return truthy.
    if( $result ) return (isset($options['fn']) ? $options['fn']() : true);

    // Otherwise, return falsey.
    return (isset($options['inverse']) ? $options['inverse']() : false);

  }

  // Returns the first value that is not undefined or the `default` value otherwise.
  public static function default( ...$values ) {

    // Extract options and the default.
    $options = array_last($values);
    $default = array_last(array_head($values));
    $values = array_head(array_head($values));

    foreach( $values as $value ) {

      if( isset($value) ) return $value;

    }

    return (isset($default) ? $default : null);

  }

  // Inline and block helper that returns truthy if `a` is equal to `b`.
  public static function eq( $a, $b, $options ) {

    $result = ($a === $b);

    if( $result ) return (isset($options['fn']) ? $options['fn']() : true);

    return (isset($options['inverse']) ? $options['inverse']() : false);

  }

  // Inline and block helper that returns truthy if `a` is greater than `b`.
  public static function gt( $a, $b, $options ) {

    $result = ($a > $b);

    if( $result ) return (isset($options['fn']) ? $options['fn']() : true);

    return (isset($options['inverse']) ? $options['inverse']() : false);

  }

  // Inline and block helper that returns truthy if `a` is greater than or equal to `b`.
  public static function gte( $a, $b, $options ) {

    $result = ($a >= $b);

    if( $result ) return (isset($options['fn']) ? $options['fn']() : true);

    return (isset($options['inverse']) ? $options['inverse']() : false);

  }

  // Inline and block helper that returns truthy if `a` is less than `b`.
  public static function lt( $a, $b, $options ) {

    $result = ($a < $b);

    if( $result ) return (isset($options['fn']) ? $options['fn']() : true);

    return (isset($options['inverse']) ? $options['inverse']() : false);

  }

  // Inline and block helper that returns truthy if `a` is less than or equal to `b`.
  public static function lte( $a, $b, $options ) {

    $result = ($a <= $b);

    if( $result ) return (isset($options['fn']) ? $options['fn']() : true);

    return (isset($options['inverse']) ? $options['inverse']() : false);

  }

  // Inline and block helper that returns truthy if `value` has `pattern`.
  public static function has( $value, $pattern, $options ) {

    $arguments = func_get_args();
    $options = array_last($arguments);
    $pattern = func_num_args() > 2 ? $pattern : false;
    $result = false;

    if( $pattern ) {

      if( is_array($value) ) {

        if( is_associative_array($value) ) $result = array_key_exists($pattern, $value);

        else $result = in_array($pattern, $value);

      }

      if( is_string($value) ) $result = strpos($value, $pattern) !== false;

    }

    if( $result ) return (isset($options['fn']) ? $options['fn']() : true);

    return (isset($options['inverse']) ? $options['inverse']() : false);

  }

  // Returns truthy if the given `value` is falsey. [opposite of isTruthy]
  public static function isFalsey( $value, $options ) {

    $result = false;

    $keywords = [
      'nada',
      'nil',
      'nay',
      'nah',
      'negative',
      'no',
      'none',
      'nope',
      'null',
      'nix',
      'nyet',
      'uh-oh',
      'veto',
      'zero',
      'false',
      '0'
    ];

    if( is_null($value) ) $result = true;
    if( $value === false ) $result = true;
    if( $value === 0 ) $result = true;
    if( in_array((string) $value, $keywords) ) $result = true;

    if( $result ) return (isset($options['fn']) ? $options['fn']() : true);

    return (isset($options['inverse']) ? $options['inverse']() : false);

  }

  // Returns truthy if the given `value` is truthy. [opposite of isFalsey]
  public static function isTruthy( $value, $options ) {

    $result = true;

    $keywords = [
      'nada',
      'nil',
      'nay',
      'nah',
      'negative',
      'no',
      'none',
      'nope',
      'null',
      'nix',
      'nyet',
      'uh-oh',
      'veto',
      'zero',
      'false',
      '0'
    ];

    if( is_null($value) ) $result = false;
    if( $value === false ) $result = false;
    if( $value === 0 ) $result = false;
    if( in_array((string) $value, $keywords) ) $result = false;

    if( $result ) return (isset($options['fn']) ? $options['fn']() : true);

    return (isset($options['inverse']) ? $options['inverse']() : false);

  }

  // Block helper that returns truthy if the given `number` is even. [opposite of ifOdd]
  public static function ifEven( $number, $options ) {

    $result = is_numeric($number) ? $number % 2 === 0 : false;

    return ($result ? $options['fn']() : $options['inverse']());

  }

  // Block helper that returns truthy if the given `number` is odd. [opposite of ifEven]
  public static function ifOdd( $number, $options ) {

    $result = is_numeric($number) ? $number % 2 !== 0 : false;

    return ($result ? $options['fn']() : $options['inverse']());

  }

  // Block helper that returns truthy if `a` is equal to `b`. [opposite of isnt]
  public static function is( $a, $b, $options ) {

    $result = ($a == $b);

    return ($result ? $options['fn']() : $options['inverse']());

  }

  // Block helper that returns truthy if `a` is not equal to `b`. [opposite of is]
  public static function isnt( $a, $b, $options ) {

    $result = ($a != $b);

    return ($result ? $options['fn']() : $options['inverse']());

  }

  // Block helper that returns truthy if neither of the given values are truthy.
  public static function neither( $a, $b, $options ) {

    $result = (!$a and !$b);

    return ($result ? $options['fn']() : $options['inverse']());

  }

  // Block helper that always returns falsey unless `a` is equal to `b`.
  public static function unlessEq( $a, $b, $options ) {

    $result = $a !== $b;

    return ($result ? $options['inverse']() : $options['fn']());

  }

  // Block helper that always returns falsey unless `a` is greater than `b`.
  public static function unlessGt( $a, $b, $options ) {

    $result = $a <= $b;

    return ($result ? $options['inverse']() : $options['fn']());

  }

  // Block helper that always returns falsey unless `a` is greater than or equal to `b`.
  public static function unlessGteq( $a, $b, $options ) {

    $result = $a < $b;

    return ($result ? $options['inverse']() : $options['fn']());

  }

  // Block helper that always returns falsey unless `a` is less than `b`.
  public static function unlessLt( $a, $b, $options ) {

    $result = $a >= $b;

    return ($result ? $options['inverse']() : $options['fn']());

  }

  // Block helper that always returns falsey unless `a` is less than or equal to `b`.
  public static function unlessLteq( $a, $b, $options ) {

    $result = $a > $b;

    return ($result ? $options['inverse']() : $options['fn']());

  }

}

?>
