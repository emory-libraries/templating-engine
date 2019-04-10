<?php

// Intialize utility methods.
trait XML_Utilities {
  
  // Escape HTML within XML.
  private function __escapeHTML( string $data, array $escape ) {
    
    // Initialize the result.
    $result = $data;
    
    // Look for HTML within the XML.
    foreach( $escape as $tag ) {
      
      // Build the regex used to locate HTML.
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
  
}

// Creates an `XML` class for handling XML data.
class XML {
  
  // Load traits.
  use XML_Utilities;
  
  // Store the XML.
  public $xml;
  
  // Constructor
  function __construct( string $data, $escape = [] ) {
    
    // Escape any HTML within the XML data.
    $data = $this->__escapeHTML($data, $escape);
    
    // Parse the XML data.
    $this->xml = new SimpleXMLElement($data);
    
  }
  
}

?>