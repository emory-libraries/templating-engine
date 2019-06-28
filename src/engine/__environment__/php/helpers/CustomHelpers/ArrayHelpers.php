<?php

namespace CustomHelpers;

trait ArrayHelpers {

  // Filter an array of objects to extract only items containing a given key-value pair.
  public static function filterWhere( array $collection, $key, $value, $comparator, array $options ) {

    // Initialize an internally recognized value of undefined to help with filtering.
    $undefined = 'FILTERWHERE_UNDEFINED';

    // Extract all items within the array collection.
    $items = array_values(array_filter($collection, function($item) {

      // Capture only array items that are associative.
      return (is_array($item) and is_associative_array($item));

    }));

    // Ignore arrays that don't contain any filterable items.
    if( empty($items) ) return [];

    // Set the comparator to unstrict equals by default.
    if( !in_array($comparator, ['==', '===', '>', '>=', '<=', '<', '>']) ) $comparator = '==';

    // Filter the items by key-value pair.
    $items = array_filter($items, function($item) use ($key, $value, $comparator, $undefined) {

      // Look for the value by key within the item.
      $val = array_get($item, $key, $undefined);

      // If the value was not found, then immediately filter out the item.
      if( $val === $undefined ) return false;

      // Otherwise, verify that the value passes the filter comparison.
      return Conditional::expression("$val $comparator $value");

    });

    // Return the filtered collection.
    return $items;

  }

  // Filter an array of objects to extract only items containing a given key.
  public static function filterHas( array $collection, $key, array $options ) {

    // Extract all items within the array collection.
    $items = array_values(array_filter($collection, function($item) {

      // Capture only array items that are associative.
      return (is_array($item) and is_associative_array($item));

    }));

    // Ignore arrays that don't contain any filterable items.
    if( empty($items) ) return [];

    // Filter the items by key-value pair.
    $items = array_filter($items, function($item) use ($key, $undefined) {

      // Verify that the key was found and set.
      return array_has($item, $key);

    });

    // Return the filtered collection.
    return $items;

  }

  // Get the index of an item within an array or string.
  public static function indexOf( $haystack, $needle, array $options ) {

    // Get the index of the item within the array or string.
    return (is_array($haystack) ? array_search($needle, $haystack) : strpos($haystack, $needle));

  }

  // Group items within an array of objects by a given key.
  public static function keyBy( array $collection, $key, array $options ) {

    // Return the filtered collection grouped based on the given key.
    return array_key_by($collection, $key);

  }

  // Create an array from the given values.
  public static function makeArray( ...$values ) {

    // Remove options from the given values, and return the values as an array.
    return array_head($values);

  }

  // Condense an array of arrays to a single level.
  public static function condense( array $array, array $options ) {

    // Returned the condense the array by one level.
    return array_merge([], ...array_values($array));

  }

  // Get the first `n` items from an array.
  public static function firstN( array $array, int $n ) {

    // Return the first `n` items within the array.
    return array_slice($array, 0, $n);

  }

  // Get the last `n` items from an array.
  public static function lastN( array $array, int $n ) {

    // Return the last `n` items within the array.
    return array_slice($array, -$n);

  }

  // Slice an array at the given beginning and ending indices.
  public static function slice( array $array, $begin = null, $end = null ) {

    // Set the beginning and end by default.
    $begin = is_int($begin) ? $begin : 0;
    $end = is_int($end) ? $end : count($array);

    // Return the slice of the array.
    return array_slice($array, $begin, $end);

  }

  // Limit an array to the given length.
  public static function limit( array $array, int $limit ) {

    // Return the array with the limit applied.
    return array_slice($array, 0, $limit);

  }

  // Get the difference of an array after a limit has been applied.
  public static function limitDifference( array $array, int $limit ) {

    // Return the difference of the array with the limit applied.
    return array_slice($array, $limit);

  }

  // Push an item onto the end of an array.
  // FIXME: This `push` helper will not work because of the current limitations LightnCandy places on custom helpers. See issue [#167](https://github.com/zordius/lightncandy/issues/167).
  /*public static function push( $value, &$array, array $options ) {

    // Push the item to the array.
    array_push($array, $value);

  }*/

  // Push an item onto the beginning of an array.
  // FIXME: This `unshift` helper will not work because of the current limitations LightnCandy places on custom helpers. See issue [#167](https://github.com/zordius/lightncandy/issues/167).
  /*public static function unshift( $value, array &$array, array $options ) {

    // Push the item to the array.
    array_unshift($array, $value);

  }*/

}

?>
