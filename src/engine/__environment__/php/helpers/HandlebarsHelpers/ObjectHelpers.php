<?php

namespace HandlebarsHelpers;

trait ObjectHelpers {

  // Extend the context with the propertyes of other objects.
  // FIXME: This `extend` helper gets overwritten by the `HandlebarsLayouts::extend` helper.
  public static function extend( ...$objects ) {

    // Get the options and objects.
    $options = array_last($objects);
    $objects = array_head($objects);

    // Add options hash back into the objects.
    $objects[] = array_get($options, 'hash', []);

    // Merge all objects into a single context.
    return array_merge(...$objects);

  }

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

  // Take arguments and, if they are a string or number, convert them to a dot-delimited object property path.
  public static function toPath( ...$properties ) {

    // Get properties without options.
    $properties = array_head($properties);

    // Build a dot-delimited object path from the given properties.
    return implode('.', array_values(array_filter($properties, function($property) {

      // Ignore non-strings and non-integers.
      return (is_string($property) or is_int($property));

    })));

  }

  // Inline and block helper to get a value from the context using dot-delimited notation.
  public static function get( $property, array $context, $options ) {

    // Get the value.
    $value = array_get($context, $property);

    // Render the block if given.
    if( isset($options['fn']) ) return (isset($value) ? $options['fn']($value) : $options['inverse']($context));

    // Otherwise, return the value.
    return $value;

  }

  // Use a dot-delimited property (`a.b.c`) path to get an object from the context.
  public static function getObject( $property, array $context ) {

    // Get the object.
    return (array_has($context, $property) ?  [$property => array_get($context, $property)] : []);

  }

  // Returns truthy if `key` is an own, enumerable property of the given `context` object.
  public static function hasOwn( array $context, $key ) { return array_key_exists($key, $context); }

  // Returns truthy if a `value` is an object.
  public static function isObject( $value ) { return (is_array($value) and is_associative_array($value)); }

  // Parses the given JSON string. [aliased as parseJSON]
  public static function JSONparse( $string ) {

    return json_decode($str, true);

  }

  // Parses the given JSON string. [alias for JSONparse]
  public static function parseJSON( $string ) {

    return forward_static_call('HandlebarsHelpers\ObjectHelpers::JSONparse', $string);

  }

  // Stringify an object as JSON. [aliased as stringifyJSON]
  public static function JSONstringify( $object, $indent = false ) {

    // Set the default indentation.
    if( is_array($indent) ) $indent = false;

    // Stringify to JSON.
    return ((bool) $indent ? json_encode($object, JSON_PRETTY_PRINT) : json_encode($object));

  }

  // Stringify an object as JSON. [alias for JSONstringify]
  public static function stringifyJSON( $object, $indent = false ) {

    return forward_static_call('HandlebarsHelpers\ObjectHelpers::JSONstringify', $object, $indent);

  }

  // Recursively merge the properties of the given `objects` with the context.
  public static function merge( ...$objects ) {

     // Get the options and objects.
    $options = array_last($objects);
    $objects = array_head($objects);

    // Add options hash back into the objects.
    $objects[] = array_get($options, 'hash', []);

    // Merge all objects into a single context.
    return array_merge_recursive(...$objects);

  }

  // Pick properties from the context object.
  public static function pick( $properties, array $context, $options ) {

    // Get the keys.
    $keys = is_array($properties) ? $properties : [$properties];

    // Initialize an index, and get the length of the keys.
    $length = count($keys);
    $index = -1;

    // Initialize the result.
    $result = [];

    // Get objects from the context.
    while( ++$index < $length ) {

      // Capture the object and save it to the results.
      $result = array_merge([], $result, forward_static_call('HandlebarsHelpers\ObjectHelpers::getObject', $keys[$index], $context));

    }

    // If a block was used, then render the block.
    if( isset($options['fn']) ) return (count($result) > 0 ? $options['fn']($result) : $options['inverse']($context));

    // Otherwise, return the result.
    return $result;

  }

}

?>
