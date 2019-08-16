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
    if( is_string($ref) ) {

      // Try to parse using the date class.
      if( \Date::parse($ref)['datetime'] !== null ) {

        // If it can be parsed using the date class, then use it.
        $ref = \Moment\Moment::fromDateTime(\Date::parse($ref)['datetime']);

      }

      // Otherwise, just parse it using datetime.
      else $ref = \Moment\Moment::fromDateTime(new DateTime($ref));

    }
    if( is_string($comp) ) {

      // Try to parse using the date class.
      if( \Date::parse($comp)['datetime'] !== null ) {

        // If it can be parsed using the date class, then use it.
        $comp = \Moment\Moment::fromDateTime(\Date::parse($comp)['datetime']);

      }

      // Otherwise, just parse it using datetime.
      else $comp = \Moment\Moment::fromDateTime(new DateTime($comp));

    }

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
    if( is_string($ref) ) {

      // Try to parse using the date class.
      if( \Date::parse($ref)['datetime'] !== null ) {

        // If it can be parsed using the date class, then use it.
        $ref = \Moment\Moment::fromDateTime(\Date::parse($ref)['datetime']);

      }

      // Otherwise, just parse it using datetime.
      else $ref = \Moment\Moment::fromDateTime(new DateTime($ref));

    }
    if( is_string($comp) ) {

      // Try to parse using the date class.
      if( \Date::parse($comp)['datetime'] !== null ) {

        // If it can be parsed using the date class, then use it.
        $comp = \Moment\Moment::fromDateTime(\Date::parse($comp)['datetime']);

      }

      // Otherwise, just parse it using datetime.
      else $comp = \Moment\Moment::fromDateTime(new DateTime($comp));

    }

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
    if( is_string($ref) ) {

      // Try to parse using the date class.
      if( \Date::parse($ref)['datetime'] !== null ) {

        // If it can be parsed using the date class, then use it.
        $ref = \Moment\Moment::fromDateTime(\Date::parse($ref)['datetime']);

      }

      // Otherwise, just parse it using datetime.
      else $ref = \Moment\Moment::fromDateTime(new DateTime($ref));

    }
    if( is_string($comp) ) {

      // Try to parse using the date class.
      if( \Date::parse($comp)['datetime'] !== null ) {

        // If it can be parsed using the date class, then use it.
        $comp = \Moment\Moment::fromDateTime(\Date::parse($comp)['datetime']);

      }

      // Otherwise, just parse it using datetime.
      else $comp = \Moment\Moment::fromDateTime(new DateTime($comp));

    }

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
    if( is_string($ref) ) {

      // Try to parse using the date class.
      if( \Date::parse($ref)['datetime'] !== null ) {

        // If it can be parsed using the date class, then use it.
        $ref = \Moment\Moment::fromDateTime(\Date::parse($ref)['datetime']);

      }

      // Otherwise, just parse it using datetime.
      else $ref = \Moment\Moment::fromDateTime(new DateTime($ref));

    }
    if( is_string($comp) ) {

      // Try to parse using the date class.
      if( \Date::parse($comp)['datetime'] !== null ) {

        // If it can be parsed using the date class, then use it.
        $comp = \Moment\Moment::fromDateTime(\Date::parse($comp)['datetime']);

      }

      // Otherwise, just parse it using datetime.
      else $comp = \Moment\Moment::fromDateTime(new DateTime($comp));

    }

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
    if( is_string($ref) ) {

      // Try to parse using the date class.
      if( \Date::parse($ref)['datetime'] !== null ) {

        // If it can be parsed using the date class, then use it.
        $ref = \Moment\Moment::fromDateTime(\Date::parse($ref)['datetime']);

      }

      // Otherwise, just parse it using datetime.
      else $ref = \Moment\Moment::fromDateTime(new DateTime($ref));

    }
    if( is_string($comp) ) {

      // Try to parse using the date class.
      if( \Date::parse($comp)['datetime'] !== null ) {

        // If it can be parsed using the date class, then use it.
        $comp = \Moment\Moment::fromDateTime(\Date::parse($comp)['datetime']);

      }

      // Otherwise, just parse it using datetime.
      else $comp = \Moment\Moment::fromDateTime(new DateTime($comp));

    }

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
  public static function momentIsBetween( $ref, $compA, $compB, $unit = 'seconds', $inclusivity = '()' ) {

    // Set locale.
    \Moment\Moment::setLocale("en_US");

    // Covert reference and comparison dates to moments.
    if( is_string($ref) ) {

      // Try to parse using the date class.
      if( \Date::parse($ref)['datetime'] !== null ) {

        // If it can be parsed using the date class, then use it.
        $ref = \Moment\Moment::fromDateTime(\Date::parse($ref)['datetime']);

      }

      // Otherwise, just parse it using datetime.
      else $ref = \Moment\Moment::fromDateTime(new DateTime($ref));

    }
    if( is_string($compA) ) {

      // Try to parse using the date class.
      if( \Date::parse($compA)['datetime'] !== null ) {

        // If it can be parsed using the date class, then use it.
        $compA = \Moment\Moment::fromDateTime(\Date::parse($compA)['datetime']);

      }

      // Otherwise, just parse it using datetime.
      else $compA = \Moment\Moment::fromDateTime(new DateTime($compA));

    }
    if( is_string($compB) ) {

      // Try to parse using the date class.
      if( \Date::parse($compB)['datetime'] !== null ) {

        // If it can be parsed using the date class, then use it.
        $compB = \Moment\Moment::fromDateTime(\Date::parse($compB)['datetime']);

      }

      // Otherwise, just parse it using datetime.
      else $compB = \Moment\Moment::fromDateTime(new DateTime($compB));

    }

     // Set the default unit.
    $unit = (!isset($unit) or is_array($unit)) ? 'seconds' : $unit;

    // Set the default inclusivity.
    $inclusivity = (!isset($inclusivity) or is_array($inclusivity)) ? '()' : $inclusivity;

    // Capture before and after.
    $isBefore = $compA->isBefore($ref, $unit);
    $isBeforeOrSame = ($isBefore or $compA->isSame($ref, $unit));
    $isAfter = $compB->isAfter($ref, $unit);
    $isAfterOrSame = ($isAfter or $compB->isSame($ref, $unit));

    // Determine inclusivity.
    $includesBefore = str_starts_with($inclusivity, '[');
    $includesAfter = str_ends_with($inclusivity, ']');

    // Determine if the moment is between the two other moments.
    if( !$includesBefore and !$includesAfter ) return ($isBefore === true and $isAfter === true);
    if( $includesBefore and !$includesAfter ) return ($isBeforeOrSame === true and $isAfter === true);
    if( !$includesBefore and $includesAfter ) return ($isBefore === true and $isAfterOrSame === true);
    return ($isBeforeOrSame === true and $isAfterOrSame === true );

  }

  // Create a moment from the given string in a given format.
  public static function momentFrom( $string, $formats, array $options ) {

    // Create the moment from the given format(s).
    return (in_array($string, ['now', 'today']) ? new \Moment\Moment() : \Moment\Moment::createFromFormat(is_array($formats) ? $formats[0] : $formats, $string, null, new \Moment\CustomFormats\MomentJs()));

  }

  // Modify a moment using the native moment API.
  public static function momentAPI( $moment, $options = null ) {

    // Set locale.
    \Moment\Moment::setLocale("en_US");

    // If a moment was not given, and only the API was initialized, then swap the passed arguments.
    $options = func_num_args() === 1 ? $moment : $options;
    $moment = func_num_args() === 1 ? new \Moment\Moment() : $moment;

    // Extract API settings from the given options.
    $settings = array_get($options, 'hash', []);

    // If any of the following keywords were given, then initialize the moment using today's date.
    if( in_array($moment, ['now', 'today']) ) $moment = new \Moment\Moment();

    // If a moment was not given, then try to convert the given thing to a moment.
    if( ($moment instanceof \Moment\Moment) === false ) $moment = array_get($settings, 'fromFormat', false) ? forward_static_call('\CustomHelpers\DateHelpers::momentFrom', $moment ?? array_get($settings, 'date', 'now'), $settings['fromFormat'], []) : new \Moment\Moment($moment ?? array_get($settings, 'date', 'now'));

    // Only continue if a valid moment exists.
    if( strpos($moment->format('Y-m-d H:i'), 'invalid') !== false ) return;

    // Get a list of operations.
    $operations = array_values(array_filter(array_keys($settings), function($key) {

      // Ignore non-method keys.
      return !in_array($key, ['date', 'fromFormat', 'order']);

    }));

    // Get the intended order of operations.
    $order = array_get($settings, 'order', count($operations) == 1 ? $operations[0] : false);

    // Convert the moment to an array if it was given as a string.
    if( is_string($order) ) $order = array_map('trim', preg_split('/[,;]? +|\./', $order));

    // Remove any settings that are known non-methods.
    unset($settings['date']);
    unset($settings['fromFormat']);
    unset($settings['order']);

    // Requre than an order be given (for two or more operations), or always return the moment as is.
    if( $order === false ) return $moment;

    // Build an API middleman to better align Moment.php methods with the Moment.js API.
    // TODO: Add in ISO and UNIX methods.
    // TODO: Add `max` and `min` methods.
    // TODO: Add `from` method.
    // TODO: Add missing display methods.
    // TODO: Add missing query methods.
    $api = [
      'add' => function($amount, $units) use (&$moment) {

        // Determine the moment method based on the units given.
        switch($units) {
          case 'millisecond':
          case 'milliseconds':
            $method = 'addSeconds';
            $amount /= 1000;
            break;
          case 'second':
          case 'seconds':
            $method = 'addSeconds';
            break;
          case 'minute':
          case 'minutes':
            $method = 'addMinutes';
            break;
          case 'hour':
          case 'hours':
            $method = 'addHours';
            break;
          case 'day':
          case 'days':
            $method = 'addDays';
            break;
          case 'week':
          case 'weeks':
            $method = 'addWeeks';
            break;
          case 'month':
          case 'months':
            $method = 'addMonths';
            break;
          case 'quarter':
          case 'quarters':
            $method = 'addMonths';
            $amount = ($amount - $moment->getQuarter()) * 3;
            break;
          case 'year':
          case 'years':
            $method = 'addYears';
            break;
        }

        // Execute the add method.
        $moment->{$method}($amount);

      },
      'subtract' => function($amount, $units) use (&$moment) {

        // Determine the moment method based on the units given.
        switch($units) {
          case 'millisecond':
          case 'milliseconds':
            $method = 'subtractSeconds';
            $amount /= 1000;
            break;
          case 'second':
          case 'seconds':
            $method = 'subtractSeconds';
            break;
          case 'minute':
          case 'minutes':
            $method = 'subtractMinutes';
            break;
          case 'hour':
          case 'hours':
            $method = 'subtractHours';
            break;
          case 'day':
          case 'days':
            $method = 'subtractDays';
            break;
          case 'week':
          case 'weeks':
            $method = 'subtractWeeks';
            break;
          case 'month':
          case 'months':
            $method = 'subtractMonths';
            break;
          case 'quarter':
          case 'quarters':
            $method = 'subtractMonths';
            $amount = ($amount - $moment->getQuarter()) * 3;
            break;
          case 'year':
          case 'years':
            $method = 'subtractYears';
            break;
        }

        // Execute the add method.
        $moment->{$method}($amount);

      },
      'millisecond' => function( $value = null ) use (&$moment) {

        // Set seconds if a value was given.
        if( isset($value) ) $moment->setSecond($value / 1000);

        // Otherwise, get seconds.
        else return ($moment->getSecond() / 1000);

      },
      'second' => function( $value = null ) use (&$moment) {

        // Set seconds if a value was given.
        if( isset($value) ) $moment->setSecond($value);

        // Otherwise, get seconds.
        else return $moment->getSecond();

      },
      'minute' => function( $value = null ) use (&$moment) {

        // Set seconds if a value was given.
        if( isset($value) ) $moment->setMinute($value);

        // Otherwise, get seconds.
        else return $moment->getMinute();

      },
      'hour' => function( $value = null ) use (&$moment) {

        // Set seconds if a value was given.
        if( isset($value) ) $moment->setHour($value);

        // Otherwise, get seconds.
        else return $moment->getHour();

      },
      'date' => function( $value = null ) use (&$moment) {

        // Set seconds if a value was given.
        if( isset($value) ) $moment->setDay($value);

        // Otherwise, get seconds.
        else return $moment->getDay();

      },
      'month' => function( $value = null ) use (&$moment) {

        // Set seconds if a value was given.
        if( isset($value) ) $moment->setMonth($value);

        // Otherwise, get seconds.
        else return $moment->getMonth();

      },
      'year' => function( $value = null ) use (&$moment) {

        // Set seconds if a value was given.
        if( isset($value) ) $moment->setYear($value);

        // Otherwise, get seconds.
        else return $moment->getYear();

      },
      'quarter' => function( $value = null ) use (&$moment) {

        // Get the current month.
        $month = $moment->getMonth();

        // Determine the month's current quarter.
        $quarter = $moment->getQuarter();

        // Set quarter if a value was given.
        if( isset($value) ) {

          // Determine the difference in quarters from the current quarter.
          $difference = $value - $quarter;

          // Set the moment's quarter.
          $moment->setMonth($difference * 3);

        }

        // Otherwise, get quarter.
        else return $quarter;

      },
      'week' => function( $value = null ) use (&$moment) {

        // Get the week number of the moment.
        $week = (int) $moment->format('W');

        // Set week if a value was given.
        if( isset($value) ) {

          // Determine the difference in weeks from the current week.
          $difference = $value - $week;

          // Determine if the weeks should be subtracted.
          if( $difference < 0 ) $moment->subtractWeeks(abs($difference));

          // Otherwise, add the weeks.
          else $moment->addWeeks($difference);

        }

        // Otherwise, get week.
        else return $week;

      },
      'day' => function( $value = null ) use (&$moment) {

        // Get the weekday number of the moment.
        $weekday = (int) $moment->format('w');

        // Set weekday if a value was given.
        if( isset($value) ) {

          // Determine the difference in weekdays from the current weekday.
          $difference = $value - $weekday;

          // Determine if the weekday should be subtracted.
          if( $difference < 0 ) $moment->subtractDays(abs($difference));

          // Otherwise, add the weekdays.
          else $moment->addDays($difference);

        }

        // Otherwise, get weekday.
        else return $weekday;

      },
      'dayOfYear' => function( $value = null ) use (&$moment) {

        // Get the day of the year.
        $day = $moment->format('z');

        // Set day of year if a value was given.
        if( isset($value) ) {

          // Determine the difference in days from the current day of the year.
          $difference = $value - $day;

          // Determine if the day should be subtracted.
          if( $difference < 0 ) $moment->subtractDays(abs($difference));

          // Otherwise, add the days.
          else $moment->addDays($difference);

        }

        // Otherwise, get day of the year.
        else return $day;

      },
      'weeksInYear' => function( $value = null ) use ($moment) {

        // Get the year.
        $year = $moment->getYear();

        // Get the weeks in the year.
        return (($moment->setISODate($year, 53))->format('W') === '53' ? 53 : 52);

      },
      'daysInMonth' => function() use ($moment) {

        // Return the last day of the month.
        return (int) $moment->format('t');

      },
      'diff' => function( $comp, $units = 'milliseconds', $precision = false ) use (&$moment) {

        // Get the interval between the dates.
        $diff = $moment->from($comp);

        // Return the difference between the dates.
        switch($units) {
          case 'milliseconds': return $precision ? $diff->getSeconds() * 1000 : round($diff->getSeconds() * 1000);
          case 'seconds': return $precision ? $diff->getSeconds() : round($diff->getSeconds());
          case 'minutes': return $precision ? $diff->getMinutes() : round($diff->getMinutes());
          case 'hours': return $precision ? $diff->getHours() : round($diff->getHours());
          case 'weeks': return $precision ? $diff->getWeeks() : round($diff->getWeeks());
          case 'days': return $precision ? $diff->getDays() : round($diff->getDays());
          case 'months': return $precision ? $diff->getMonths() : round($diff->getMonths());
          case 'years': return $precision ? $diff->getYears() : round($diff->getYears());
        }

      },
      'format' => function($format) use (&$moment) {

        // Format the date using the Moment.js formats.
        return $moment->format($format, new \Moment\CustomFormats\MomentJs());

      },
      'set' => function($units, $amount) use (&$api) {

        // Determine the setter method to be used based on the units.
        switch($units) {
          case 'millisecond':
          case 'milliseconds':
          case 'ms':
            return $api['millisecond']($amount);
          case 'second':
          case 'seconds':
          case 's':
            return $api['second']($amount);
          case 'minute':
          case 'minutes':
          case 'm':
            return $api['minute']($amount);
          case 'hour':
          case 'hours':
          case 'h':
            return $api['hour']($amount);
          case 'date':
          case 'dates':
          case 'D':
            return $api['date']($amount);
          case 'month':
          case 'months':
          case 'M':
            return $api['month']($amount);
          case 'year':
          case 'years':
          case 'y':
            return $api['year']($amount);
        }

      },
      'get' => function($units) use (&$api) {

        // Determine the setter method to be used based on the units.
        switch($units) {
          case 'millisecond':
          case 'milliseconds':
          case 'ms':
            return $api['millisecond']();
          case 'second':
          case 'seconds':
          case 's':
            return $api['second']();
          case 'minute':
          case 'minutes':
          case 'm':
            return $api['minute']();
          case 'hour':
          case 'hours':
          case 'h':
            return $api['hour']();
          case 'date':
          case 'dates':
          case 'D':
            return $api['date']();
          case 'month':
          case 'months':
          case 'M':
            return $api['month']();
          case 'year':
          case 'years':
          case 'y':
            return $api['year']();
        }

      },
      'startOf' => function($period) use (&$moment) {

        // Move the moment to the start of the given time period.
        $moment->startOf($period);

      },
      'endOf' => function($period) use (&$moment) {

        // Move the moment to the end of the given time period.
        $moment->endOf($period);

      }
    ];

    // Add aliases for API methods.
    $api['milliseconds'] = &$api['millisecond'];
    $api['seconds'] = &$api['second'];
    $api['minutes'] = &$api['minute'];
    $api['hours'] = &$api['hour'];
    $api['dates'] = &$api['date'];
    $api['months'] = &$api['month'];
    $api['weeks'] = $api['weekYear'] = &$api['week'];
    $api['days'] = $api['weekdays'] = $api['weekday'] = &$api['day'];
    $api['years'] = &$api['year'];
    $api['quarters'] = &$api['quarter'];

    // Manipulate the moment based on the given order of operations.
    foreach( $order as $method ) {

      // Only continue modifying the moment if it's still in moment form.
      if( ($moment instanceof \Moment\Moment) === false ) continue;

      // Skip the method if a matching method setting was not given or if the method doesn't exist.
      if( !array_key_exists($method, $settings) or !isset($api[$method]) ) continue;

      // Otherwise, get the method's value.
      $value = $settings[$method];

      // For select methods, enable an easier-to-use string syntax for passing arguments.
      if( in_array($method, ['add', 'subtract']) ) $value = array_map(function($value) {

        // Explode the value's parts, and trim them.
        return array_map('trim', explode(' ', $value));

      }, explode(', ', $value));

      // Otherwise, for all other values, convert it to an array form if it wasn't given as one.
      else if( !is_array($value) ) $value = [$value];

      // For add/subtract methods, apply the additions/subtractions in order.
      if( in_array($method, ['add', 'subtract']) ) {

        // Loop through each pair of values, and execute its operation.
        foreach( $value as $args ) { $api[$method](...$args); }

      }

      // For getter/setter methods, allow empty values to indicate that the getter should be used.
      else if( in_array($method, [
        'millisecond', 'milliseconds',
        'second', 'seconds',
        'minute', 'minutes',
        'hour', 'hours',
        'date', 'dates',
        'day', 'days',
        'weekday',
        'isoWeekday',
        'dayOfYear',
        'week', 'weeks',
        'isoWeek', 'isoWeeks',
        'month', 'months',
        'quarter', 'quarters',
        'year', 'years',
        'weekYear',
        'isoWeekYear',
        'weeksInYear',
        'isoWeeksInYear',
        'valueOf',
        'unix',
        'daysInMonth',
        'toDate',
        'toArray',
        'toJSON',
        'toISOString',
        'toObject',
        'inspect'
      ]) and in_array($value[0], [true, null]) ) $moment = $api[$method]();

      // For getter methods that accept arguments, manipulate the moment but capture the output.
      else if( in_array($method, [
        'format',
        'fromNow',
        'from',
        'toNow',
        'to',
        'calendar',
        'difference',
        'valueOf',
        'unix',
        'daysInMonth',
        'toDate',
        'toArray',
        'toJSON',
        'toISOString',
        'toObject',
        'inspect'
      ]) ) $moment = isset($value) ? $api[$method](...$value) : $api[$method]();

      // Otherwise, directly manipulate the moment using the given method.
      else $api[$method](...$value);

    }

    // Return the modified moment.
    return $moment;

  }

}

?>
