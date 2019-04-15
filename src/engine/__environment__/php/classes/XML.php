<?php

/*
 * XML
 *
 * Parses a string of XML data into an object or array.
 */
class XML {
  
  // Escapes HTML within a string of XML data.
  public static function escapeHTML( string $xml, array $escape ) {
    
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
  
  // Converts a string of XML data into a object.
  public static function toObject( string $xml, $escape = [] ) {
    
    // Return a SimpleXMLElement object.
    return new SimpleXMLElement(self::escapeHTML($xml, $escape));
    
  }
  
  // Converts a string of XML data into an array.
  public static function toArray( string $xml, $escape = [] ) {
    
    // Return an associative array.
    return object_to_array(new SimpleXMLElement(self::escapeHTML($xml, $escape)));
    
  }
  
}

?>