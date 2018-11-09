<?php

// Build number casts.
trait Cast_Number {
  
  private function int() { return (int) $this->data; }

  private function integer() { return $this->int(); }
  
  private function float() { return (float) $this->data; }
  
  private function double() { return $this->float(); }
  
}

// Build date casts.
trait Cast_Date {
  
  private function date() {
    
    return (new DateTime())->setTimestamp(strtotime($this->data)); 
    
  }
  
}

// Build boolean casts.
trait Cast_Boolean {
  
  private function bool() {
    
    switch($this->data) {
      case 'true': return true;
      case 'false': return false;
      default: return (bool) $this->data;
    }
    
  }
  
  private function boolean() { return $this->bool(); }
  
}

// Build array casts.
trait Cast_List {
  
  private function array() {
    
    // Initialize helpers for cleaning strings.
    $trim = function( $string, $chars = [' '], $start = true, $end = true) {
      
      $chars = implode('|', array_map('preg_quote', $chars));
      
      if( $start ) $string = preg_replace_all("/^$chars/", '', $string);
      if( $end ) $string = preg_replace_all("/$chars$/", '', $string);
      
      return $string;
      
    };
    
    // Strip any wrappers from the the string.
    $string = $trim($this->data, [' ', '[', ']', '(', ')', '{', '}']);
    
    // Convert to an array, and trim all values.
    $array = array_map($trim, explode(',', $string));
    
    // Clean up array values.
    foreach( $array as $key => $value ) {
      
      // Create associative values wherever possible.
      if( strpos(':', $value) !== false ) {
        
        $split = explode(':', $value, 2);
        
        $array[$trim($split[0], [' ', "'", '"'])] = $trim($split[1], [' ', "'", '"']);
        
        unset($array[$key]);
        
      }
      
      // Attempt to cast each array value.
      else {
        
        $array[$key] = new Cast($value);
        
      }
      
    }
    
    // Return.
    return $array;
    
  }
  
  private function list() {
    
    return $this->array();
    
  }
  
}

// Build text casts.
trait Cast_Text {
  
  private function string() { return $this->data; }
  
  private function text() { return $this->string(); }
  
}

// Build null casts.
trait Cast_Null {
  
  private function null() { return null; }
  
}

// Creates a `Cast` class for easy typification.
class Cast {
  
  // Use helpers.
  use Cast_Number, Cast_Date, Cast_Boolean, Cast_List, Cast_Text, Cast_Null;
  
  // Determine input data type.
  private $is_array = false;
  
  // Capture the data.
  protected $data;
  
  // Determine the type.
  protected $type = 'string';
  
  // Define regular expressions.
  private $regex = [
    'array' => '/^((?:\S|\ )+?,)+?(\S|\ )+?$/',
    'list' => '/^([^.?!]+?[,;] ?)+([^.?!]+)$/'
  ];
  
  // Constructor
  function __construct( $data ) {
    
    // Set the input data type.
    if( is_array($data) ) $this->is_array = true;
    
    // Save the data.
    $this->data = $data;
    
    // Determine the data type.
    if( $this->is_array ) $this->typeAll();
    else $this->type();
    
  }
  
  // Check data type.
  private function type() { 
    
    // Check numeric types.
    if( is_numeric($this->data) ) $this->type = strpos($this->data, '.') !== false ? 'float' : 'int';
    
    // Check for date types.
    else if( preg_match('/\d/', $this->data) and (bool) strtotime($this->data) ) $this->type = 'date';
    
    // Check for array types.
    else if( preg_match($this->regex['array'], $this->data) ) $this->type = 'array';
    
    // Check for boolean types.
    else if( in_array(((string) $this->data), ['true', 'false']) ) $this->type = 'bool';
    
    // Check for list types.
    else if( preg_match($this->regex['list'], $this->data) ) $this->type = 'list';
    
    // Check for null types.
    else if( in_array($this->data, ['null', null]) ) $this->type = 'null';
    
  }
  
  // Check data type for arrays.
  private function typeAll() {
    
    foreach( $this->data as $key => $value ) {
      
      $this->data[$key] = new Cast($value);
      
    }
    
  }
  
  // Cast the value to its data type.
  public function cast( $deep = true ) {
    
    // Neatly handle arrays.
    if( $this->is_array ) return $this->castAll($deep);
      
    // Otherwise, handle non-arrays.
    else {
    
      // Generate a shallow cast.
      $cast = $this->{$this->type}();

      // Initialize a helper for handling recursive casts.
      $recursive = function( array $array ) {

        foreach( $array as $key => $value ) {

          if( is_array($value) ) $array[$key] = $recursive($value);

          else if( $value instanceof Cast ) $array[$key] = $value->cast(true);

        }

        return $array;

      };

      // Perform a deep cast (recursive).
      if( $deep and is_array($cast) ) $cast = $recursive($cast);

      // Return the cast.
      return $cast;
      
    }
    
  }
  
  // Cast the value to its data type for arrays.
  public function castAll( $deep = true ) {

    // Neatly handle non-arrays.
    if( !$this->is_array ) return $this->cast($deep);
      
    // Otherwise, handle arrays.
    else {
    
      // Cast all values within the array.
      foreach( $this->data as $key => $value ) {
        
        // Save the casted array value.
        $this->data[$key] = $value->cast($deep); 
      
      }

      // Return the casted array.
      return $this->data;
      
    }
    
  }
  
}

?>