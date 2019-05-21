<?php

namespace HandlebarsHelpers;

trait DateHelpers {
  
  // Returns the current year, optionally formatted using the given `format`.
  public static function year( $format = 'YYYY' ) {
    
    // Set the default format.
    if( is_array($format) ) $format = 'YYYY';
    
    // Output the year.
    return (new Moment\Moment())->format($format, new Moment\CustomFormats\MomentJs());
    
  }
  
  // Uses moment as a helper. [aliased as date]
  public static function moment( $date = null, $format = 'MMMM DD, YYYY', $options = [] ) {
    
    // Get arguments.
    $arguments = func_get_args();
    $options = array_last($arguments);
    $format = func_num_args() == 3 ? $format : (func_num_args() == 2 ? $date : 'MMMM DD, YYYY');
    $date = func_num_args() == 3 ? $date : null;
    
    // Use moment.js formats for formatting outputs.
    $formats = new Moment\CustomFormats\MomentJs();
    
    // Set the locale.
    Moment\Moment::setLocale('en_US');
    
    // Return today's formatted date if none was given.
    if( is_null($date) ) return (new Moment\Moment())->format($format, $formats);
    
    // Return a formatted date given strings. 
    if( is_string($date) ) return (Moment\Moment::fromDateTime((new Date($date))->date['datetime']))->format($format, $formats);
    
    // Return a formatted date if given an object.
    if( is_array($date) and is_associative_array($date) ) return (new Moment\Moment($date))->format($format, $formats);
    
    // Return a formatted date if given a date object.
    if( is_a($date, 'Date') ) return (Moment\Moment::fromDateTime($date->date['datetime']))->format($format, $formats);
    
    // Return a formatted date if given a moment object.
    if( is_a($date, 'Moment\Moment') ) return $date->format($format, $formats);
  
    // Return a formatted date if given a datetime object.
    if( is_a($date, 'DateTime') ) return (Moment\Moment::fromDateTime($date))->format($format, $formats);
    
    // Otherwise, return a formatted date.
    return (new Moment\Moment($date))->format($format, $formats);
    
  }
  
  // Uses moment as a helper. [alias for moment]
  public static function date( $date, $format, $options ) {
    
    return forward_static_call('HandlebarsHelpers\DateHelpers::moment', $date, $format, $options);
    
  }
  
}

?>