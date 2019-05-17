<?php

namespace HandlebarsHelpers;

use _;

trait NumberHelpers {
  
  // Format a number to its equivalent in bytes, or for strings, get the string's size in bytes.
  public static function bytes( $number, $precision = 2, $options = [] ) {
    
    // For empty, non-string, and/or non-numeric values, return 0 bytes.
    if( is_null($number) and !is_string($number) and !is_numeric($number) ) return '0 B';
    
    // For string values, get the length of the string.
    if( is_string($number) ) $number = strlen($number);
    
    // Get options and precision.
    $options = func_num_args() == 3 ? $options : $precision;
    $precision = func_num_args() == 3 ? $precision : 2;
    
    // Set byte abbreviations.
    $abbr = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    
    // Get precision as a power of 10.
    $precision = pow(10, $precision);
    
    // Get the index of the last item in the byte abbreviations.
    $index = count($abbr) - 1;
    
    // Determine if appropriate abbreviation for the number.
    while( $index-- >= 0 ) {
      
      // Get the bytes size as a power of 10.
      $size = pow(10, $index * 3);
      
      // Determine if the bytes size matches the number.
      if( $size <= ($number + 1) ) {
        
        // Get the number with the correct precision.
        $number = round($number * $precision / $size) / $precision;
        
        // Capture the abbreviation.
        $number .= " {$abbr[$index]}";
        
        // Stop looking for the correct abbreviation.
        break;
        
      }
      
    }
    
    // Return the number in bytes.
    return $number;
    
  }
  
  // Add commas to numbers.
  public static function addCommas( $number ) {
    
    // Get the number of decimal places in the number.
    $decimals = strlen(substr(strrchr((string) $number, '.'), 1));
    
    // Then, format the number with commas.
    return number_format($number, $decimals);
    
  }
  
  // TODO: Create definition for `phoneNumber` helper.
  
  // TODO: Create definition for `toAbbr` helper.
  
  // TODO: Create definition for `toExponential` helper.
  
  // TODO: Create definition for `toFixed` helper.
  
  // TODO: Create definition for `toFloat` helper.
  
  // TODO: Create definition for `toInt` helper.
  
  // TODO: Create definition for `toPrecision` helper.
  
}

?>