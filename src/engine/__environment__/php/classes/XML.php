<?php

/*
 * XML
 *
 * Parses a string of XML data into an object or array.
 */
class XML {

  // Escapes HTML within a string of XML data.
  protected static function escapeHTML( string $xml, array $escape ) {

    // Initialize the result.
    $result = $xml;

    // Look for HTML within the XML.
    foreach( $escape as $tag ) {

      // Build the regex used to locate the HTML.
      $regex = "/(?:\<{$tag}\>)((?:(?:\n\r?)*?|.*?)*?)(?:\<\/{$tag}\>)/";

      // Find any HTML that should be escaped.
      if( preg_match_all($regex, $result, $matches, PREG_SET_ORDER) ) {

        // Escape the HTML one by one.
        foreach( $matches as $match ) {

          // Escape the HTML.
          $result = str_replace($match[0], "<{$tag}>".htmlspecialchars($match[1])."</{$tag}>", $result);

        }

      }

    }

    // Return the result.
    return $result;

  }

  // Escape query strings within a string of XML data.
  protected static function escapeQueryStrings( string $xml ) {

    // Initialize the result.
    $result = $xml;

    // Build the regex use to locate query strings.
    $regex = '/(\?[[:word:][:punct:]]+?\=.+)(\&(?!amp;).+\=.+?(?=\<|\&|$))+/';

    // Look for query strings within the XML.
    if( preg_match_all($regex, $result, $matches, PREG_SET_ORDER) ) {

      // Escape the query strings one by one.
      foreach( $matches as $match ) {

        // Escape the query string.
        $result = str_replace($match[0], strtr($match[1], ['&' => '&amp;']), $result);

      }

    }

    // Return the result.
    return $result;

  }

  // Escape things within the XML string prior to parsing it.
  public static function escapeXML( string $xml, array $escape ) {

    // Escape HTML.
    $xml = self::escapeHTML($xml, $escape);

    // Escape query strings.
    $xml = self::escapeQueryStrings($xml);

    // Return the escaped XML.
    return $xml;

  }

  // Converts a string of XML data into a object.
  public static function toObject( string $xml, $escape = [] ) {

    // Return a SimpleXMLElement object.
    return new SimpleXMLElement(self::escapeHTML($xml, $escape));

  }

  // Converts a string of XML data into an array.
  public static function toArray( string $xml, $escape = [] ) {

    // Return an associative array.
    return object_to_array(new SimpleXMLElement(self::escapeXML($xml, $escape)));

  }

}

?>
