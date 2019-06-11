<?php

namespace CustomHelpers;

trait DateHelpers {
  
  // Get a datetime object from a string date.
  public static function datetime( $string, array $options ) {
    
    return Cast::toDate($string);
    
  }
  
}

?>