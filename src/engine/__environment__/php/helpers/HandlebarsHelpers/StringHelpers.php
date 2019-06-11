<?php

namespace HandlebarsHelpers;

trait StringHelpers {
  
  // Append the specified `suffix` to the given `string`.
  public static function append( $string, $suffix ) {
    
    // Append the suffix to the string.
    return ((is_string($string) and is_string($suffix)) ? $string.$suffix : $string);
    
  }
  
  // Prepends the specified `prefix` to the given `string`.
  public static function prefix( $string, $prefix ) {
    
    // Append the suffix to the string.
    return ((is_string($string) and is_string($prefix)) ? $prefix.$string : $string);
    
  }
  
  // Converts a string to camelcase.
  public static function camelcase( $string ) { return (is_string($string) ? camelcase($string) : ''); }
  
  // Capitalize the first word in a sentence.
  public static function capitalize( $string ) { return (is_string($string) ? ucfirst($string) : ''); }
  
  // Capitalize all words in a string.
  public static function capitalizeAll( $string ) { return (is_string($string) ? implode(' ', array_map('ucfirst', explode(' ', $string))) : ''); }
  
  // Center a string using non-breaking spaces.
  public static function center( $string, $spaces ) {
    
    // Initialize the spacer.
    $spacer = str_repeat('&nbsp;', $spaces);
    
    // Return the centered string.
    return $spacer.$string.$spacer;
    
  }
  
  // Trims a string of extraneous whitespace and non-word characters.
  public static function chop( $string ) { return (is_string($string) ? trim(preg_replace('/^\W+|\W+$/', '', $string)) : ''); }
  
  // Converts a string to dashcase.
  public static function dashcase( $string ) { return (is_string($string) ? kebabcase($string) : ''); }
  
  // Converts a string to dotcase.
  public static function dotcase( $string ) { return (is_string($string) ? dotcase($string) : ''); }
  
  // A block and inline helper that converts a string to lowercase. [aliased as downcase]
  public static function lowercase( $string ) {
    
    // Get options.
    $options = func_num_args() > 1 ? array_last(func_get_args()) : $string;
    $string = func_num_args() > 1 ? $string : '';
    
    // Lowercase the string or block.
    return (isset($options['fn']) ? strtolower($options['fn']()) : strtolower($string));
    
  }
  
  // A block and inline helper that converts a string to lowercase. [alias for lowercase]
  public static function downcase( $string ) {
    
    // Get options.
    $options = func_num_args() > 1 ? array_last(func_get_args()) : $string;
    $string = func_num_args() > 1 ? $string : '';
    
    // Use the lowercase helper.
    return forward_static_call('HandlebarsHelpers\StringHelpers::lowercase', $string, $options);
    
  }
  
  // Truncates a string to the specified `length`, and appends it with an ellipsis `…`.
  public static function ellipsis( $string, $limit ) {
    
    // Return the truncated string with an ellipsis added.
    return (is_string($string) ? (strlen($string) <= $limit ? $string : substr($string, 0, $limit).'…') : '');
    
  }
  
  // Truncates a string to the specified `length`.
  public static function truncate( $string, $limit, $suffix ) {
    
    // Set the default suffix.
    $suffix = func_num_args() > 3 ? $suffix : '';
    
    // Return the truncated string.
    return (is_string($string) ? (strlen($string) <= $limit ? $string : substr($string, 0, $limit - strlen($suffix)).$suffix) : '');
    
  }
  
  // Truncates a words in a string to the specified `length`.
  public static function truncateWords( $string, $count, $suffix ) {
    
    // Ignore invalid values.
    if( !is_string($string) or !is_int($count) ) return '';
    
    // Set the default suffix.
    $suffix = func_num_args() > 3 ? $suffix : '…';
    
    // Get the words within the string.
    $words = preg_split('/[ \t]/', $string);
    
    // Return the truncated string.
    return ($count < count($words) ? trim(implode(' ', array_slice($words, 0, $count))).$suffix : $string);
    
  }
  
  // Replaces spaces in a string with hyphens.
  public static function hyphenate( $string ) { return (is_string($string) ? strtr($string, ' ', '-') : ''); }
  
  // Returns truthy if a `value` is a string.
  public static function isString( $string ) { return is_string($string); }
  
  // Returns the number of occurrences of `substring` within the given `string`.
  public static function occurrences( $string, $substring ) { return (is_string($string) ? substr_count($string, $substring) : ''); }
  
  // Converts a string to pascalcase.
  public static function pascalcase( $string ) { return (is_string($string) ? ucfirst(camelcase($string)) : ''); }
  
  // Converts a string to pathcase.
  public static function pathcase( $string ) { return (is_string($string) ? pathcase($string) : ''); }
  
  // Replaces spaces in a string with pluses.
  public static function plusify( $string, $character ) { 
    
    // Set the default character.
    $character = func_num_args() > 2 ? $character : ' ';
    
    // Return the string with plus signs added.
    return (is_string($string) ? strtr($string, $character, '+') : '');
  
  }
  
  // Renders a block without processing mustache templates inside the block.
  public static function raw( $options ) {
    
    // Get the block's rendered contents.
    $contents = $options['fn']([]);
    
    // Get the context.
    $context = array_merge([], array_get($options['_this'], 'options', []), array_get($options, 'hash', []));
    
    // Merge named options into context if given.
    if( isset($options['name']) and isset($context[$options['name']]) ) $context = array_merge([], $context[$options['name']], $context);
    
    // Escape the content block unless escaping was disabled.
    if( array_get($context, 'escape', true) !== false ) {
      
      // Initialize the index.
      $index = 0;
      
      // Find all mustache templates within the block, and escape them.
      while( ($index = strpos($contents, '{{', $index)) !== false ) {
        
        // Only escape non-escaped mustache templates.
        if( isset($contents[$index - 1]) and $contents[$index - 1] !== '\\' ) $contents = substr($contents, 0, $index).'\\'.substr($contents, $index);
        
        // Increment the index.
        $index += 3;
        
      }
      
    }
    
    // Return the block.
    return $contents;
    
  }
  
  // Removes all occurrence of `substring` from the given `string`.
  public static function remove( $string, $substring ) {
    
    // Return the string without the substring.
    return (is_string($string) ? (is_string($substring) ? str_replace($substring, '', $string) : $string) : '');
    
  }
  
  // Remove the first occurrence of `substring` from the given `string`.
  public static function removeFirst( $string, $substring ) {

    // Return the string without the substring.
    return (is_string($string) ? (is_string($substring) ? str_replace_first($substring, '', $string) : $string) : '');
    
  }
  
  // Replace all occurrences of substring `a` with substring `b`.
  public static function replace( $string, $a, $b ) {
    
    // Set the default replacement string.
    $b = func_num_args() > 3 ? $b : '';
    
    // Replace all instances of the `a` with `b`.
    return (is_string($string) ? (is_string($a) ? str_replace($a, $b, $string) : $string) : '');
    
  }
  
  // Replace the first occurrence of substring `a` with substring `b`.
  public static function replaceFirst( $string, $a, $b ) {
    
    // Set the default replacement string.
    $b = func_num_args() > 3 ? $b : '';
    
    // Replace all instances of the `a` with `b`.
    return (is_string($string) ? (is_string($a) ? str_replace_first($a, $b, $string) : $string) : '');
    
  }
  
  // Convert a string to sentence case.
  public static function sentence( $string ) {
    
    // Ignore non-strings.
    if( !is_string($string) ) return '';
    
    // Initialize sentences.
    $sentences = [];
    
    // Split the string into sentences.
    preg_match_all('/((?:\S[^\.\?\!]*)[\.\?\!]*)/', $string, $sentences);

    // Convert the sentences to sentence case.
    $sentences = array_map(function($sentence) {
      
      // Trim extraneous whitespace, then capitalize the first character, and lowercase all other characters.
      return ucfirst(strtolower(trim($sentence)));
      
    }, $sentences[0]);
    
    // Combine and return the sentences.
    return implode(' ', $sentences);
    
  }
  
  // Convert a string to snakecase.
  public static function snakecase( $string ) { return (is_string($string) ? snakecase($string) : ''); }
  
  // Split a string by the given `character`.
  public static function split( $string, $character ) { return (is_string($string) ? (is_string($character) ? explode($character, $string) : $string ) : ''); }
  
  // A block helper that tests whether a string begines with the given `prefix`.
  public static function startsWith( $prefix, $string, $options ) {
    
    return (str_starts_with($string, $prefix) ? $options['fn']() : $options['inverse']());
    
  }
  
  // Converts a given string to titlecase.
  public static function titleize( $string ) {
    
    // Ignore non-strings.
    if( !is_string($string) ) return '';
    
    // Replace consecutive dashes and/or underscores with a space.
    $title = preg_replace('/[-_]+/', ' ', $string);
    
    // Then, capitalize each word.
    return implode(' ', array_map('ucfirst', explode(' ', $string)));
    
  }
  
  // Removes extraneous whitespace from the beginning and end of a string.
  public static function trim( $string ) { return (is_string($string) ? trim($string) : ''); }
  
  // Removes extraneous whitespace from the beginning of a string.
  public static function trimLeft( $string ) { return (is_string($string) ? ltrim($string) : ''); }
  
  // Removes extraneous whitespace from the end of a string.
  public static function trimRight( $string ) { return (is_string($string) ? rtrim($string) : ''); }
  
  // A block and inline helper that converts a string to uppercase. [aliased as upcase]
  public static function uppercase( $string ) {
    
    // Get options.
    $options = func_num_args() > 1 ? array_last(func_get_args()) : $string;
    $string = func_num_args() > 1 ? $string : '';
    
    // Lowercase the string or block.
    return (isset($options['fn']) ? strtoupper($options['fn']()) : strtoupper($string));
    
  }
  
  // A block and inline helper that converts a string to uppercase. [alias for uppercase]
  public static function upcase( $string ) {
    
    // Get options.
    $options = func_num_args() > 1 ? array_last(func_get_args()) : $string;
    $string = func_num_args() > 1 ? $string : '';

    // Use the uppercase helper.
    return forward_static_call('HandlebarsHelpers\StringHelpers::uppercase', $string, $options);
    
  }
  
}

?>