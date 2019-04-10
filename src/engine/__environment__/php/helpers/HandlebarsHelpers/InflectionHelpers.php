<?php

namespace HandlebarsHelpers;

use _;

trait InflectionHelpers {
  
  // Returns either the `singular` or `plural` inflection of a word based on the given `count`.
  public static function inflect( $count, $singular, $plural, $counter = false ) {
    
    // Set the counter to not show by default.
    if( is_array($counter) ) $counter = false;
    
    // Get the word with inflection.
    $word = ($count > 1 || $count === 0) ? $plural : $singular;
    
    // Return the word, optionally including the counter.
    return ($counter ? "$count $word" : $word);
    
  }
  
  // Returns an ordanilzed number as a string.
  public static function ordinalize( $value ) {
    
    // Ignore non-numbers.
    if( !is_float($value) and !is_int($value) ) return $value;
    
    // Get the absolute value of the number.
    $number = abs(round($value));
    
    // Get the oridinal remainder when divided by 100.
    $remainder = $number % 100;
    
    // Handle special ordinal cases.
    if( in_array($remainder, [11, 12, 13]) ) return "{$value}th";
    
    // Get the original remainder when divided by 10.
    $remainder = $number % 10;
    
    // Handle all other oridinal cases.
    switch($remainder) {
      case 1: return "{$value}st";
      case 2: return "{$value}nd";
      case 3: return "{$value}rd";
      default: return "{$value}th";
    }
    
  }
  
}

?>