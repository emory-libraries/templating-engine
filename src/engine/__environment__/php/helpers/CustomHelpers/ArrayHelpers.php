<?php

namespace CustomHelpers;

trait ArrayHelpers {

  // Filter an array of objects to extract only items containing a given key-value pair.
  public static function filterWhere( array $collection, $key, $value, $comparator = '==' ) {

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
    if( !in_array($comparator, ['==', '===', '>', '>=', '<=', '<', '>', '!==', '!=']) ) $comparator = '==';

    // Filter the items by key-value pair.
    $items = array_filter($items, function($item) use ($key, $value, $comparator, $undefined) {

      // Look for the value by key within the item.
      $val = array_get($item, $key, $undefined);

      // If the value was not found, then immediately filter out the item.
      if( $val === $undefined ) return false;

      // Otherwise, verify that the value passes the filter comparison.
      return Conditional::expression(var_export(is_string($val) ? strtr($val, ['/' => '\/']) : $val, true)." $comparator ".var_export($value, true));

    });

    // Return the filtered collection.
    return $items;

  }

  // Filter an array of objects to extract only items not containing a given key-value pair.
  public static function filterWhereNot( array $collection, $key, $value, $comparator = '==' ) {

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
    if( !in_array($comparator, ['==', '===', '>', '>=', '<=', '<', '>', '!==', '!=']) ) $comparator = '==';

    // Filter the items by key-value pair.
    $items = array_filter($items, function($item) use ($key, $value, $comparator, $undefined) {

      // Look for the value by key within the item.
      $val = array_get($item, $key, $undefined);

      // If the value was not found, then immediately filter out the item.
      if( $val === $undefined ) return false;

      // Swap the given comparator for it's opposite.
      $comparator = [
        '==' => '!=',
        '===' => '!==',
        '>' => '<=',
        '>=' => '<',
        '<' => '>=',
        '<=' => '>',
        '!==' => '===',
        '!=' => '=='
      ][$comparator];

      // Otherwise, verify that the value passes the filter comparison.
      return Conditional::expression(var_export(is_string($val) ? strtr($val, ['/' => '\/']) : $val, true)." $comparator ".var_export($value, true));

    });

    // Return the filtered collection.
    return $items;

  }

  // Filter an array of objects to extract only items containing a given key.
  public static function filterHas( array $collection, $key, array $options ) {

    // Initialize an internally recognized value of undefined to help with filtering.
    $undefined = 'FILTERWHERE_UNDEFINED';

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

  // Filter an array of objects to extract only items missing a given key.
  public static function filterHasNot( array $collection, $key, array $options ) {

    // Initialize an internally recognized value of undefined to help with filtering.
    $undefined = 'FILTERWHERE_UNDEFINED';

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
      return !array_has($item, $key);

    });

    // Return the filtered collection.
    return $items;

  }

  // Get the index of an item within an array or string.
  public static function indexOf( $haystack, $needle, array $options ) {

    // Get the index of the item within the array or string.
    return index_of($needle, $haystack);

  }

  // Extract items by key, assigning them to the given key.
  public static function keyBy( array $collection, $key, array $options ) {

    // Return the keyed collection.
    return array_key_by($collection, $key);

  }

  // Group items by key.
  public static function groupBy( array $coloection, $key, array $options ) {

    // Return the grouped collection.
    return array_group_by($collection, $key);

  }

  // Create an array from the given values.
  public static function makeArray( ...$values ) {

    // Remove options from the given values, and return the values as an array.
    return array_head($values);

  }

  // Condense an array of arrays to a single level.
  public static function condense( array $array, array $options ) {

    // Returned the condense the array by one level.
    foreach ($array as $key => $val ) {

      if( !(is_array($val)) ) {
        $array[$key] = array($val);
      }

    }
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

  // Concatenate two or more arrays.
  public static function concat( ...$arrays ) {

    // Remove the options from the arrays set.
    $arrays = array_head($arrays);

    // Concatenate the arrays.
    return array_merge(...$arrays);

  }

  // Push one or more items onto the end of an array.
  public static function push( array $array, ...$values ) {

    // Remove the options from the values.
    $values = array_head($values);

    // Push the item to the array.
    foreach( $values as $value ) { array_push($array, $value); }

    // Return the array with the values added.
    return $values;

  }

  // Push one or more items onto the beginning of an array.
  public static function unshift( array $array, ...$values ) {

    // Remove the options from the values.
    $values = array_head($values);

    // Unshift the item to the array.
    foreach( $values as $value ) { array_unshift($array, $value); }

    // Return the array with the values added.
    return $values;

  }

}

?>
