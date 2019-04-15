<?php

/**
 * Date
 *
 * Helps parse date strings into valid dates.
 */
class Date {
  
  protected static $patterns = [
    
    // Standard US datetime shorthand(s), using 1-2 digit months and days and 2-4 digit years, i.e.:
    //
    // 01/01/2019
    // 01-01-2019
    // 01.01.2019
    // 01/01/2019 12:00 am
    // 01-01-2019 12:00pm
    // 01.01.2019 12:00
    '(?P<month>\d{1,2})(?P<seperator>[\/\.\-])(?:(?P<day>\d{1,2})\k<seperator>)?(?P<year>\d{2,4})( (?P<hour>\d{1,2})\:(?P<minute>\d{1,2}) ?(?P<meridian>am|pm)?)?',
    
    
    // Standard US datetime string(s), using full or abbreviated month name (with or without a period) with optional missing day, i.e.:
    //
    // January 1, 2019
    // Jan. 1, 2019
    // January 2019
    // January 1, 2019 12:00 am
    // Jan. 1, 2019 12:00pm
    // January 2019 12:00
    '(?P<month>\w{3,})\.? (?:(?P<day>\d{1,2})\, )?(?P<year>\d{2,4})( (?P<hour>\d{1,2})\:(?P<minute>\d{1,2}) ?(?P<meridian>am|pm)?)?'
    
  ];
  
  protected static $months = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December'
  ];
  
  protected static $days_in_month = [
    31,
    28,
    31,
    30,
    31,
    30,
    31,
    31,
    30,
    31,
    30,
    31
  ];
  
  protected static $leap_month = 'February';
  
  protected static $time_format = '12h';
  
  protected static $weekdays = [
    'Sunday',
    'Monday', 
    'Tuesday',
    'Wednesday',
    'Thursday', 
    'Friday',
    'Saturday'
  ];
  
  public $date;
  
  // Constructs a date from a string.
  function __construct( $string ) {
    
    $this->date = self::parse($string);
  
  }
  
  // Parses a string to a date.
  public static function parse( $string ) {
    
    // Get today's date for use as a reference.
    $today = new DateTime();
    
    // Try to parse the string by testing it against each of the datetime patterns.
    $patterns = array_values(array_filter(array_map(function($pattern) use ($string) {
      
      preg_match("/$pattern/i", $string, $matches);
      
      return $matches;
      
    }, self::$patterns), function($matches) {
      
      return count($matches) > 0;
      
    }));
    
    // Determine if the date could be parsed.
    $date = count($patterns) > 0 ? $patterns[0] : false;
    
    // Further parse and validate seemingly valid dates.
    if( $date ) {
      
      // Remove numeric indices.
      foreach( $date as $index => $value ) { if( is_numeric($index) ) unset($date[$index]); }
      
      // Get date and time parts.
      $month = $date['month'];
      $day = $date['day'];
      $year = $date['year'];
      $hour = isset($date['hour']) ? $date['hour'] : null;
      $minute = isset($date['minute']) ? $date['minute'] : null;
      $meridian = isset($date['meridian']) ? $date['meridian'] : null; 
          
      // Continue parsing date and time parts.
      $date['string'] = $string;
      $date['month'] = is_numeric($month) ? (int) $month : array_search($month, self::$months) + 1;
      
      // Validate that the month is value.
      if( $date['month'] < 1 or $date['month'] > 12 ) return false;
   
      // Continue parsing data and time parts.
      $date['month_name'] = self::$months[$date['month'] - 1];
      $date['day'] = is_numeric($day) ? (int) $day : 1;
      $date['days_in_month'] = self::$days_in_month[$date['month'] - 1];
      $date['year'] = strlen($year) == 2 ? (int) substr($today->format('Y'), 0, -2).$year : (int) $year;
      $date['is_leap_month'] = $date['month_name'] == self::$leap_month;
      $date['is_leap_year'] = (bool) date('L', mktime(0, 0, 0, 1, 1, $year));
      $date['hour_'.self::$time_format] = is_numeric($hour) ? (int) $hour : 0;
      $date['minute'] = is_numeric($minute) ? (int) $minute : 0;
      $date['meridian'] = isset($meridian) ? strtolower($meridian) : 'am';
 
      // Validate the date and time.
      if( !$date['month'] or !$date['month_name'] or !$date['days_in_month'] ) return false;
      if( strlen((string) $date['year']) !== 4 ) return false;
      if( $date['is_leap_month'] and $date['is_leap_year'] ) $date['days_in_month']++;
      if( $date['day'] < 1 or $date['day'] > $date['days_in_month'] ) return false;
      if( !in_array($date['meridian'], ['am', 'pm']) ) $date['meridian'] = 'am';
      if( $date['hour_'.self::$time_format] < 0 or $date['hour_'.self::$time_format] > 24 ) return false;
      if( $date['minute'] < 0 or $date['minute'] > 59 ) return false;
      if( self::$time_format == '12h' ) { 
        $date['hour_12h'] = $date['hour_12h'] % 12 ?: 12;
        $date['hour_24h'] = $date['hour_12h'];
        if( $date['meridian'] == 'am' and $date['hour_12h'] == 12 ) $date['hour_24h'] -= 12;
        if( $date['meridian'] == 'pm' and $date['hour_12h'] < 12 ) $date['hour_24h'] += 12;
      }
      if( self::$time_format == '24h' ) {
        if( $date['meridian'] == 'am' and $date['hour_24h'] == 12 ) $date['hour_24h'] -= 12;
        if( $date['meridian'] == 'pm' and $date['hour_24h'] < 12 ) $date['hour_24h'] += 12;
        $date['hour_12h'] = $date['hour_24h'] % 12 ?: 12;
      }

      // Extract date and time parts in order to generate a datetime object.
      $month = $date['month'];
      $day = $date['day'];
      $year = $date['year'];
      $hour = $date['hour_24h'];
      $minute = $date['minute'];

      // Create a datetime object.
      $datetime = (new DateTime())->setDate($year, $month, $day)->setTime($hour, $minute);
    
      // Save additional data about the date and time.
      $date['datetime'] = $datetime;
      $date['month'] = $month;
      $date['month_start'] = 1;
      $date['month_end'] = $date['days_in_month'];
      $date['hour'] = $date['hour_'.self::$time_format];
      $date['weekday'] = ($weekday = $datetime->format('l'));
      $date['day_of_week'] = array_search($weekday, self::$weekdays) + 1;

    }
    
    // Return the date.
    return $date;
    
  }
  
}

?>