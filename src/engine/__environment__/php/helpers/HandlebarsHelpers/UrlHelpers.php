<?php

namespace HandlebarsHelpers;

trait UrlHelpers {
  
  // Encodes a URI component by escaping certain characters. [aliased as url_encode]
  public static function encodeURI( $string ) { return (is_string($string) ? rawurlencode($string): null); }
  
  // Decoes a URI component by unescaping certain characters. [aliased as url_decode]
  public static function decodeURI( $string ) { return (is_string($string) ? rawurldecode($string) : null); }
  
  // Encode a URI component. [alias for encodeURI]
  public static function url_encode( $string ) {
    
    // Encode the URI.
    return forward_static_call('HandlebarsHelpers\UrlHelpers::encodeURI', $string);
    
  }
  
  // Decode a URI component. [alias for decodeURI]
  public static function url_decide( $string ) {
    
    // Encode the URI.
    return forward_static_call('HandlebarsHelpers\UrlHelpers::decodeURI', $string);
    
  }
  
  // Escape the given string by replacing characters with escape sequences.
  public static function escape( $string ) {
    
    // Locate a query string within the given string.
    $url = explode('?', $string);
    
    // Initialize a helper method for escaping a qurey string.
    $escape = function( string $query ) {
      
      // Parse the query string, and get its query parameters.
      parse_str($query, $params);
      
      // Escape all query parameters.
      $params = array_map('urlencode', $params);
      
      // Return the escaped parameters as a query string.
      return http_build_query($params);
      
    };
    
    // If the string was a full URL, then escape only the query string portion.
    if( count($url) > 1 ) $url[1] = $escape($url[1]);
    
    // Otherwise, if only a query string was given, then escape it.
    else $url[0] = $escape($url[0]);
    
    // Return the escaped string.
    return implode('?', $url);
    
  }
  
  // Take the base URL, and a href URL, and resolve them as a browser would for an anchor tag.
  public static function urlResolve( $base, $href ) { return Sabre\Uri\resolve($base, $href); }
  
  // Parses a `url` string into an object.
  public static function urlParse( $url ) { return Sabre\Uri\parse($url); }
  
  // Strip the query string form the given `url`.
  public static function stripQuerystring( $url ) { return (is_string($url) ? explode('?', $url)[0] : null); }
  
  // Strip protocol from a `url`. 
  public static function stripProtocol( $url ) { 
  
    // Ignore non-strings.
    if( !is_string($url) ) return;
    
    // Parse the URL.
    $parsed = Sabre\Uri\parse($url);
    
    // Clear the protocol.
    $parsed['scheme'] = null;
    
    // Rebuild the URL without the protocol.
    return Sabre\Uri\build($parsed);
  
  }
  
}

?>