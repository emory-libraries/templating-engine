<?php

namespace CustomHelpers;

trait DateHelpers {
  
  // Get a datetime object from a string date.
  public static function datetime( $string, array $options ) {
    
    return Cast::toDate($string);
    
  }
  
  // Check if a moment is before another moment.
  public static function momentIsBefore( $ref, $comp, $unit ) {
    
    // Set locale.
    \Moment\Moment::setLocale("en_US");
    
    // Covert reference and comparison dates to moments.
    if( is_string($ref) ) $ref = \Moment\Moment::fromDateTime(\Date::parse($ref)['datetime']);
    if( is_string($comp) ) $comp = \Moment\Moment::fromDateTime(\Date::parse($comp)['datetime']);

    // Set the default unit.
    $unit = (!isset($unit) or is_array($unit)) ? 'seconds' : $unit;

    // Determine if the moment is before.
    return $ref->isBefore($comp, $unit);

  }

  // Check if a moment is before today.
  public static function momentIsBeforeToday( $ref, $unit ) {
    
    // Forward the call to another helper.
    return forward_static_call('CustomHelpers\DateHelpers::momentIsBefore', $ref, Moment\Moment(), $unit);
      
  }

  // Check if a moment is after another moment.
  public static function momentIsAfter( $ref, $comp, $unit ) {
    
    // Set locale.
    \Moment\Moment::setLocale("en_US");
    
    // Covert reference and comparison dates to moments.
    if( is_string($ref) ) $ref = \Moment\Moment::fromDateTime(\Date::parse($ref)['datetime']);
    if( is_string($comp) ) $comp = \Moment\Moment::fromDateTime(\Date::parse($comp)['datetime']);

    // Set the default unit.
    $unit = (!isset($unit) or is_array($unit)) ? 'seconds' : $unit;

    // Determine if the moment is after.
    return $ref->isAfter($comp, $unit);

  }

  // Check if a moment is after today.
  public static function momentIsAfterToday( $ref, $unit ) {
      
    // Forward the call to another helper.
    return forward_static_call('CustomHelpers\DateHelpers::momentIsAfter', $ref, new \Moment\Moment(), $unit);
      
  }

  // Check if a moment is the same as another moment.
  public static function momentIsSame( $ref, $comp, $unit ) {
    
    // Set locale.
    \Moment\Moment::setLocale("en_US");
    
    // Covert reference and comparison dates to moments.
    if( is_string($ref) ) $ref = \Moment\Moment::fromDateTime(\Date::parse($ref)['datetime']);
    if( is_string($comp) ) $comp = \Moment\Moment::fromDateTime(\Date::parse($comp)['datetime']);

    // Set the default unit.
    $unit = (!isset($unit) or is_array($unit)) ? 'seconds' : $unit;

    // Determine if the moment is the same.
    return $ref->isSame($comp, $unit);

  }

  // Check if a moment is the same as today.
  public static function momentIsSameToday( $ref, $unit ) {
    
    // Forward the call to another helper.
    return forward_static_call('CustomHelpers\DateHelpers::momentIsSame', $ref, new \Moment\Moment(), $unit);
      
  }

  // Check if a moment is before or the same as another moment.
  public static function momentIsSameOrBefore( $ref, $comp, $unit ) {
    
    // Set locale.
    \Moment\Moment::setLocale("en_US");
    
    // Covert reference and comparison dates to moments.
    if( is_string($ref) ) $ref = \Moment\Moment::fromDateTime(\Date::parse($ref)['datetime']);
    if( is_string($comp) ) $comp = \Moment\Moment::fromDateTime(\Date::parse($comp)['datetime']);

    // Set the default unit.
    $unit = (!isset($unit) or is_array($unit)) ? 'seconds' : $unit;
    
    // Determine if the moment is the same or before.
    return ($ref->isSame($comp, $unit) or $ref->isBefore($comp, $unit));

  }

  // Check if a moment is before or the same as today.
  public static function momentIsSameOrBeforeToday( $ref, $unit ) {
    
    // Forward the call to another helper.
    return forward_static_call('CustomHelpers\DateHelpers::momentIsSameOrBefore', $ref, new \Moment\Moment(), $unit);
      
  }

  // Check if a moment is after or the same as another moment.
  public static function momentIsSameOrAfter( $ref, $comp, $unit ) {
    
    // Set locale.
    \Moment\Moment::setLocale("en_US");
    
    // Covert reference and comparison dates to moments.
    if( is_string($ref) ) $ref = \Moment\Moment::fromDateTime(\Date::parse($ref)['datetime']);
    if( is_string($comp) ) $comp = \Moment\Moment::fromDateTime(\Date::parse($comp)['datetime']);
    
    // Set the default unit.
    $unit = (!isset($unit) or is_array($unit)) ? 'seconds' : $unit;

    // Determine if the moment is the same or after.
    return ($ref->isSame($comp, $unit) or $ref->isAfter($comp, $unit));

  }

  // Check if a moment is after or the same as today.
  public static function momentIsSameOrAfterToday( $ref, $unit ) {
    
    // Forward the call to another helper.
    return forward_static_call('CustomHelpers\DateHelpers::momentIsSameOrAfter', $ref, new \Moment\Moment(), $unit);
      
  }

  // Check if a moment is between two other moments.
  public static function momentIsBetween( $ref, $compA, $compB, $unit, $inclusivity ) {
    
    // Set locale.
    \Moment\Moment::setLocale("en_US");
    
    // Covert reference and comparison dates to moments.
    if( is_string($ref) ) $ref = \Moment\Moment::fromDateTime(\Date::parse($ref)['datetime']);
    if( is_string($compA) ) $compA = \Moment\Moment::fromDateTime(\Date::parse($compA)['datetime']);
    if( is_string($compB) ) $compB = \Moment\Moment::fromDateTime(\Date::parse($compB)['datetime']);
    
     // Set the default unit.
    $unit = (!isset($unit) or is_array($unit)) ? 'seconds' : $unit;
    
    // Capture before and after.
    $isBefore = $ref->isBefore($compA, $unit);
    $isBeforeOrSame = ($isBefore or $ref->isSame($compA, $unit));
    $isAfter = $ref->isAfter($compB, $unit);
    $isAfterOrSame = ($isAfter or $ref->isSame($compB, $unit));
    
    // Determine inclusivity.
    $includesBefore = str_starts_with('[', $inclusivity);
    $includesAfter = str_ends_with(']', $inclusivity);

    // Determine if the moment is between the two other moments.
    if( !$includesBefore and !$includesAfter ) return ($isBefore === true and $isAfter === true);
    if( $includesBefore and !$includesAfter ) return ($isBeforeOrSame === true and $isAfter === true);
    if( !$includesBefore and $includesAfter ) return ($isBefore === true and $isAfterOrSame === true);
    return ($isBeforeOrSame === true and $isAfterOrSame === true );

  }
  
}

?>