<?php

namespace HandlebarsHelpers;

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
    
    // Determine the appropriate abbreviation for the number.
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
  
  // Convert a string or number to a formatted phone number.
  public static function phoneNumber( $number ) {
    
    // Convert the number to a string.
    $number = (string) $number;
    
    // Get the phone number's parts.
    $area = substr($number, 0, 3);
    $prefix = substr($number, 3, 3);
    $line = substr($number, 6, 4);
    
    // Format the number as a phone number.
    return "($area) $prefix-$line";
    
  }
  
  // Abbreviate numbers to the given `precision` number of decimal points.
  public static function toAbbr( $number = 0, $precision = 2 ) {
    
    // Set the defaults for number and precision.
    $precision = func_num_args() > 2 ? $precision : 2;
    $number = func_num_args() > 1 ? $number : 0;
    
    // Get precision as a power of 10.
    $precision = pow(10, $precision);
    
    // Set number abbreviations.
    $abbr = ['k', 'm', 'b', 't', 'q'];
    
    // Get the index of the last item in the number abbreviations.
    $index = count($abbr) - 1;
    
    // Determine the appropriate abbreviation for the number.
    while( $index >= 0 ) {
      
      // Get the number size as a power of 10.
      $size = pow(10, ($index + 1) * 3);
      
      // Determine if the number size matches the number.
      if( $size <= ($number + 1) ) {
        
        // Get the number with the correct precision.
        $number = round($number * $precision / $size) / $precision;
        
        // Capture the abbreviation.
        $number .= $abbr[$index];
        
        // Stop looking for the correct abbreviation.
        break;
        
      }
      
      // Decrement the index.
      $index--;
      
    }
    
    // Return the abbreviated number.
    return $number;
    
  }
  
  // Return the string representing the given number in exponential notation.
  public static function toExponential( $number = 0, $digits = 0 ) {
    
    // Set the defaults for number and digits.
    $digits = func_num_args() > 2 ? $digits : 0;
    $number = func_num_args() > 1 ? $number : 0;
    
    // Get the remainder of the number when divided by 10, and its base number divisible by 10.
    $remainder = fmod($number, 10);
    $base = $number - $remainder;
    
    // Determine if the base or remainder should be used in determining places.
    $n = $base === 0 ? $remainder : $base;
    
    // Initialize a counter for capturing places.
    $places = 0;
    
    // Get the digit that should be used as the stopping point when looking for places.
    $digit = (int) ((string) $number)[0];
    
    // Capture the number of times the number can be divided by 10.
    while( $n <> $digit ) {
      
      // Increment or decrement places.
      $places = $n < 1 ? $places - 1 : $places + 1;
      
      // Reset n, and continue counting places.
      $n = $n < 1 ? $n * 10 : $n / 10;
      
    }
    
    // Capture the number's base 10 power.
    $power = pow(10, $places);
    
    // Return the number formated using exponential notation.
    return (number_format($number / $power, $digits, '.', '') . 'e'. ($places > 0 ? '+'. $places : $places));
    
  }
  
  // Formats the given number using fixed-point notation.
  public static function toFixed( $number = 0, $digits = 0 ) {
    
    // Set the defaults for number and digits.
    $digits = func_num_args() > 2 ? $digits : 0;
    $number = func_num_args() > 1 ? $number : 0;
    
    // Format the number using fixed-point notation.
    return number_format($number, $digits, '.', '');
    
  }
  
  // Cast a number to a floating point value.
  public static function toFloat( $number ) { return (float) $number; }
  
  // Cast a number to an integer value.
  public static function toInt( $number ) { return (int) $number; }
  
  // Formats the given number to the specified precision.
  public static function toPrecision( $number = 0, $precision = 1 ) {
    
    // Set the defaults for number and precision.
    $precision = func_num_args() > 2 ? $precision : 1;
    $number = func_num_args() > 1 ? $number : 0;
    
    // Always return 0 as is.
    if( $number === 0 ) return 0;
    
    // Get the digits before and after the decimal place.
    $digits = explode('.', (string) $number);
    
    // Get the number of digits to the left and right of the decimal place.
    $length = array_map('strlen', $digits);
    
    // If precision < number of digits before the decimal, then use exponential notation.
    if( $precision < $length[0] ) return forward_static_call('HandlebarsHelpers\NumberHelpers::toExponential', $number, $precision - 1);
    
    // Otherwise, find significant digits.
    $power = floor(log(abs($number), 10) + 1);
    $significant = round($number / pow(10, $power) * pow(10, $precision)) / pow(10, $precision);
    
    // Capture the significant value.
    $value = $significant * pow(10, $power);
    
    // Get the value's length.
    $length = strlen((string) $value);
    
    // If precision < number of digits in the value, the convert it to exponential notation.
    if( $precision < $length ) return forward_static_call('HandlebarsHelpers\NumberHelpers::toExponential', $value, $precision - 1);
    
    // Otherwise, if precision > number of digits in value, then fill the number with 0 digits.
    if( $precision > $length ) return $value.str_repeat('0', $precision - $length);
    
    // Otherwise, return the value as is.
    return $value;
    
  }
  
}

?>