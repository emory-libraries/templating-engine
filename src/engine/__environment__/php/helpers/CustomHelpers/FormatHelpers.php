<?php

namespace CustomHelpers;

trait FormatHelpers {

  // Define a list of states and terrories to help with formatting addresses.
  public static $states = [

    // States
    ["long" => "Alabama", "short" => "AL"],
    ["long" => "Alaska", "short" => "AK"],
    ["long" => "Arizona", "short" => "AR"],
    ["long" => "Arkansas", "short" => "AR"],
    ["long" => "California", "short" => "CA"],
    ["long" => "Colorado", "short" => "CO"],
    ["long" => "Connecticut", "short" => "CT"],
    ["long" => "Delaware", "short" => "DE"],
    ["long" => "Florida", "short" => "FL"],
    ["long" => "Georgia", "short" => "GA"],
    ["long" => "Georgia", "short" => "GA"],
    ["long" => "Hawaii", "short" => "HI"],
    ["long" => "Idaho", "short" => "ID"],
    ["long" => "Illinois", "short" => "IL"],
    ["long" => "Indiana", "short" => "IN"],
    ["long" => "Iowa", "short" => "IA"],
    ["long" => "Kansas", "short" => "KS"],
    ["long" => "Kentucky", "short" => "KY"],
    ["long" => "Louisiana", "short" => "LA"],
    ["long" => "Maine", "short" => "ME"],
    ["long" => "Maryland", "short" => "MD"],
    ["long" => "Massachusetts", "short" => "MA"],
    ["long" => "Michigan", "short" => "MI"],
    ["long" => "Minnesota", "short" => "MN"],
    ["long" => "Mississippi", "short" => "MS"],
    ["long" => "Missouri", "short" => "MO"],
    ["long" => "Montana", "short" => "MT"],
    ["long" => "Nebraska", "short" => "NE"],
    ["long" => "Nevada", "short" => "NV"],
    ["long" => "New Hampshire", "short" => "NH"],
    ["long" => "New Jersey", "short" => "NJ"],
    ["long" => "New Mexico", "short" => "NM"],
    ["long" => "New York", "short" => "NY"],
    ["long" => "North Carolina", "short" => "NC"],
    ["long" => "North Dakota", "short" => "ND"],
    ["long" => "Ohio", "short" => "OH"],
    ["long" => "Oklahoma", "short" => "OK"],
    ["long" => "Oregon", "short" => "OR"],
    ["long" => "Pennsylvania", "short" => "PA"],
    ["long" => "Rhode Island", "short" => "RI"],
    ["long" => "South Carolina", "short" => "SC"],
    ["long" => "South Dakota", "short" => "SD"],
    ["long" => "Tennessee", "short" => "TN"],
    ["long" => "Texas", "short" => "TX"],
    ["long" => "Utah", "short" => "UT"],
    ["long" => "Vermont", "short" => "VT"],
    ["long" => "Virginia", "short" => "VA"],
    ["long" => "Washington", "short" => "WA"],
    ["long" => "West Virginia", "short" => "WV"],
    ["long" => "Wisconsin", "short" => "WI"],
    ["long" => "Wyoming", "short" => "WY"],

    // Territories
    ["long" => "American Samoa", "short" => "AS"],
    ["long" => "District of Columbia", "short" => "DC"],
    ["long" => "Guam", "short" => "GU"],
    ["long" => "Marshall Islands", "short" => "MH"],
    ["long" => "Northern Marianas", "short" => "MP"],
    ["long" => "Palaus", "short" => "PW"],
    ["long" => "Puerto Rico", "short" => "PR"],
    ["long" => "Virgin Islands", "short" => "VI"],

  ];

  // Format a phone number based on the given formatting syntax.
  public static function formatPhone( $number, $format, array $options ) {

    // Get the number without any characters.
    $number = preg_replace('/[^0-9]/', '', (string) $number);

    // Get all digits by splitting the phone number into its digits.
    $digits = str_split($number);

    // Initialize an index for counting digits.
    $i = 0;

    // Return the formatted phone number.
    return implode('', array_map(function($char) use (&$i, $digits) {

      // Only update placeholder characters.
      if( in_array(strtolower($char), ['x', '0', '#']) ) {

        // Replace the placeholder character with a digit.
        $char = $digits[$i];

        // Increment the digit index.
        $i++;

      }

      // Return the character.
      return $char;

    }, str_split((string) $format)));

  }

  // Format a currency to use the given number of decimal places.
  public static function formatCurrency( $number, $decimals ) {

    // Use two decimal places by defaul.
    $decimals = func_num_args() > 2 ? $decimals : 2;

    // Return the formatted currency.
    return "$".number_format((float) $number, $decimals, '.', ',');

  }

  // Format a date to use the given formatting syntax.
  public static function formatDate( $date, string $format, array $options ) {

    // Format the date.
    return Cast::toDate($value)->format($format, new Moment\CustomFormats\MomentJs());

  }

  // Format an address based on the given formatting syntax.
  public static function formatAddress( $address, string $format, array $options ) {

    // Format the address.
    foreach( $address as $key => $value ) {

      // Find the placeholder within the format.
      if( preg_match("/\{($key(?:\..+?)?)\}/", $format, $placeholder) === 1 ) {

        // Get the placeholder ID.
        $id = explode('.', $placeholder[1]);

        // Apply any format modifications to the value.
        if( $id[0] == 'state' and in_array($id[1], ['long', 'short']) ) {

          // Use the formatted state value.
          $value = array_filter_by(CustomHelpers::$states, ['long' => $value])[0][$id[1]];

        }

        // Merge the value into the format.
        $format = str_replace($placeholder[0], $value, $format);

      }

    }

    // Return the formatted address.
    return $format;

  }

}

?>
