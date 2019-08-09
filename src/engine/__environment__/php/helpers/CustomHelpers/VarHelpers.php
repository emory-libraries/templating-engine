<?php

namespace CustomHelpers;

trait VarHelpers {

  // Set a key-value pair on a given context.
  // FIXME: This `set` helper will not work due to LightnCandy helper limitations. See issue [#167](https://github.com/zordius/lightncandy/issues/167).
  /*public static function set( $key, $value, &$context, $options = [] ) {

    // Set the key on the given context.
    $context = array_set($context, $key, $value);

  }*/

  // Unset a key-value pair on a given context.
  // FIXME: This `unset` helper will not work due to LightnCandy helper limitations. See issue [#167](https://github.com/zordius/lightncandy/issues/167).
  /*public static function unset( $key, &$context, $options = [] ) {

    // Unset the key on the given context.
    $context = array_unset($context, $key);

  }*/

  // Make an object or array from the given block.
  // FIXME: Ideally, this `make` helper could be passed a target context with the intended key within that context. However, due to LightnCandy's helper limitations, for the time being, the key must be root-relative, i.e., passed in dot-delimited notation as a reference from the root data object.
  public static function make( $key, $context, array $options ) {

    // Get the rendered content of the block.
    $content = trim($options['fn']());

    // Create the object or array on the given context.
    $options['_this'] = array_set($options['_this'], $key, Cast::cast($content));

  }

  // Assign a value to the current context.
  public static function assign( $key, $value, array $options ) {

    // Assign the value to the given key within the current context.
    $options['_this'] = array_set($options['_this'], $key, $value, true);

  }

  // Unassign a value to the current context.
  public static function unassign( $key, array $options ) {

    // Unassign the value to the given key within the current context.
    $options['_this'] = array_unset($options['_this'], $key);

  }

}

?>
