<?php

namespace HandlebarsHelpers;

use _;

trait MathHelpers {
  
  // Gets the absolute value of a number.
  public static function abs( $number ) {
    
    // Throw an error for any non-numeric values.
    if( !is_numeric($number) ) throw new Error('Expected a number');
    
    // Otherwise, get the absolute value of a number.
    return abs($number);
    
  }
  
  // Adds two numbers. [aliased as plus]
  public static function add( $a, $b ) {
    
    // Add numbers together.
    if( is_numeric($a) and is_numeric($b) ) return ($a + $b);
    
    // Otherwise, concatenate strings together.
    if( is_string($a) and is_string($b) ) return ($a . $b);
    
    // Otherwise, return nothing.
    return null;
    
  }
  
  // Gets the average of all given numbers.
  public static function avg( ...$numbers ) {
    
    // Remove the options object from the number array.
    $numbers = array_head($numbers);
    
    // Get the average of all numbers.
    return (array_sum($numbers) / count($numbers));
    
  }
  
  // Rounds a number up to its ceiling.
  public static function ceil( $number ) {
    
    // Throw an error for any non-numeric values.
    if( !is_numeric($number) ) throw new Error('Expected a number');
    
    // Otherwise, get the number's ceiling.
    return ceil($number);
    
  }
  
  // Divides two numbers.
  public static function divide( $a, $b ) {
    
    // Throw an error for any non-numeric values.
    if( !is_numeric($a) ) throw new Error('Expected the first argument to be a number');
    if( !is_numeric($b) ) throw new Error('Expected the second argument to be a number');
    
    // Divide the two numbers.
    return ($a / $b);
    
  }
  
  // Rounds an number down to its floor.
  public static function floor( $number ) {
    
    // Throw an error for any non-numeric values.
    if( !is_numeric($number) ) throw new Error('Expected a number');
    
    // Otherwise, get the number's floor.
    return floor($number);
    
  }
  
  // Subtracts two numbers. [alias for sustract]
  public static function minus( $a, $b ) {
    
    // Use the subtract helper.
    return forward_static_call('HandlebarsHelpers\MathHelpers::subtract', $a, $b);
    
  }
  
  // Gets the remainder when two numbers are divided. [aliased as remainder]
  public static function modulo( $a, $b ) {
    
  // Throw an error for any non-numeric values.
    if( !is_numeric($a) ) throw new Error('Expected the first argument to be a number');
    if( !is_numeric($b) ) throw new Error('Expected the second argument to be a number');
    
    // Modulo the two numbers.
    return ($a % $b);
    
  }
  
  // Multiplies two numbers. [aliased as times]
  public static function multiply( $a, $b ) {
    
    // Throw an error for any non-numeric values.
    if( !is_numeric($a) ) throw new Error('Expected the first argument to be a number');
    if( !is_numeric($b) ) throw new Error('Expected the second argument to be a number');
    
    // Multiply the two numbers.
    return ($a * $b);
    
  }
  
  // Adds two numbers. [alias for add]
  public static function plus( $a, $b ) {
    
    // Use the add helper.
    return forward_static_call('HandlebarsHelpers\MathHelpers::add', $a, $b);
    
  }
  
  // Generate a random number within a minimum and maximum range.
  public static function random( $min, $max ) {
    
    // Throw an error for any non-numeric values.
    if( !is_numeric($min) ) throw new Error('Expected minimum to be a number');
    if( !is_numeric($max) ) throw new Error('Expected maximum to be a number');
    
    // Get a random number between the minimum and maximum values.
    return rand($min, $max);
    
  }
  
  // Gets the remainder when two numbers are divided. [alias for modulo]
  public static function remainder( $a, $b ) {
    
    // Use the modulo helper.
    return forward_static_call('HandlebarsHelpers\MathHelpers::modulo', $a, $b);
    
  }
  
  // Rounds a number to its nearest whole number.
  public static function round( $number ) {
    
    // Throw an error for any non-numeric values.
    if( !is_numeric($number) ) throw new Error('Expected a number');
    
    // Otherwise, round the number.
    return round($number);
    
  }
  
  // Subtracts two numbers. [aliased as minus]
  public static function subtract( $a, $b ) {
    
    // Throw an error for any non-numeric values.
    if( !is_numeric($a) ) throw new Error('Expected the first argument to be a number');
    if( !is_numeric($b) ) throw new Error('Expected the second argument to be a number');
    
    // Substract the two numbers.
    return ($a - $b);
    
  }
  
  // Gets the sum of all given numbers.
  public static function sum( ...$numbers ) {
    
    // Remove the options object from the number array.
    $numbers = array_head($numbers);
    
    // Get the sum of all numbers.
    return array_sum($numbers);
    
  }
  
  // Multiples two numbers. [alias for multiply]
  public static function times( $a, $b ) {
    
    // Use the multiply helper.
    return forward_static_call('HandlebarsHelpers\MathHelpers::multiply', $a, $b);
    
  }
  
}

?>