<?php

namespace CustomHelpers;

/* StorageHelpers
 *
 * NOTE: These `StorageHelpers` are needed due to known limitations within LightnCandy's implementation of custom helpers. Because LightnCandy does not properly carry changes to child contexts upward into parent contexts, these `StorageHelpers` are a workaround to allow for context data to stored temporarly, then retrieved and reapplied on parent contexts. See issue [#268](https://github.com/zordius/lightncandy/issues/268) for more details.
 */
trait StorageHelpers {
  
  // Temporarily store a value in storage.
  public static function storageSet( $key, $value ) {
    
    Helper::set('CustomHelpers\VarHelpers::storage', $key, $value);
    
  }
  
  // Push a value onto the end of an temporary array in storage.
  public static function storagePush( $key, $value ) {
    
    // Get the array from storage.
    $array = Helper::get('CustomHelpers\VarHelpers::storage', $key, []);
    
    // Push the value to the array.
    array_push($array, $value);

    // Restore the array in storage.
    Helper::set('CustomHelpers\VarHelpers::storage', $key, $array);
    
  }
  
  // Push a value onto the beginning of an temporary array in storage.
  public static function storageUnshift( $key, $value ) {
    
    // Get the array from storage.
    $array = Helper::get('CustomHelpers\VarHelpers::storage', $key, []);
    
    // Push the value to the array.
    array_unshift($array, $value);

    // Restore the array in storage.
    Helper::set('CustomHelpers\VarHelpers::storage', $key, $array);
    
  }
  
  // Get a value temporarily stored in storage.
  public static function storageGet( $key ) {
    
    return Helper::get('CustomHelpers\VarHelpers::storage', $key);
    
  }
  
}

?>