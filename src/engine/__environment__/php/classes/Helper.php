<?php

/*
 * Helper
 *
 * Helps with handing off data from one handlebars helper to
 * another. This is mostly needed for layout helpers.
 */
class Helper {

  // Store some data temporarily.
  public static $data = [];

  // Set some data temporarily.
  public static function set( string $helper, string $key, $value ) {

    // Temporarily store the data.
    self::$data = array_set(self::$data, "$helper.$key", $value);

  }

  // Get some temporary data, then unset it.
  public static function get( string $helper, string $key, $default = null ) {

    // Get the value that was stored temporarily.
    $value = array_get(self::$data, "$helper.$key", $default);

    // Unset the temporary value.
    self::$data = array_unset(self::$data, "$helper.$key");

    // Return the value that was stored temporarily.
    return $value;

  }

  // Determine if some temporary data exists.
  public static function has( string $helper, string $key ) {

    // Look for the key within the temporary data set.
    return array_has(self::$data, $key);
    
  }

}

?>
