<?php

use Moment\Moment;

/**
 * Mutator
 *
 * Mutates a given data model to reflect 
 * a more desirable data model based on a
 * set of template-specific mutations.
 */
class Mutator {
  
  // Mutate some data based on a set of template-specific data mutations.
  public static function mutate( array $data, string $template ) {
    
    // Look for any template-specific mutations.
    $mutations = array_get(CONFIG['config']['mutations'], $template);
  
    // Return the unmutated data if no mutations exist.
    if( !isset($mutations) ) return $data;

    // Otherwise, mutate the data, starting by making repeatable areas.
    $data = self::repeatable($data, array_get($mutations, 'repeatable', []));
    
    // Then, mutate checkboxes.
    $data = self::checkbox($data, array_get($mutations, 'checkbox', []));
    
    // Then, mutate radio buttons.
    $data = self::radio($data, array_get($mutations, 'radio', []));
    
    // Then, mutate timestamps.
    $data = self::timestamp($data, array_get($mutations, 'timestamp', []));
    
    // Then, mutate text.
    $data = self::text($data, array_get($mutations, 'text', []));
    
    // Then, mutate html.
    $data = self::html($data, array_get($mutations, 'html', []));
    
    // Then, alias things.
    $data = self::alias($data, array_get($mutations, 'alias', []));
    
    // Then, rename things.
    $data = self::rename($data, array_get($mutations, 'rename', []));
    
    // Then, replace things.
    $data = self::replace($data, array_get($mutations, 'replace', []));
    
    // Then, remove things.
    $data = self::remove($data, array_get($mutations, 'remove', []));
    
    // Then, add things.
    $data = self::add($data, array_get($mutations, 'add', []));
    
    // Return the mutated data.
    return $data;
    
  }
  
  // Make values within a data set repeatable by forcing them to be non-associative arrays.
  public static function repeatable( array $data, $repeatables ) {
    
    // Convert strings to an array.
    $repeatables = is_string($repeatables) ? [$repeatables] : $repeatables;
      
    // Make the keys repeatable one by one.
    foreach( $repeatables as $repeatable ) { 
      
      // Look for the key within the data set.
      if( array_get($data, $repeatable, false) !== false ) {
        
        // Get the existing value.
        $value = array_get($data, $repeatable);
        
        // Force the key's value to be a non-associative array.
        if( is_array($value) and is_associative_array($value) ) {
          
          // Mutate the value.
          $data = array_set($data, $repeatable, [$value]);
          
        }
        
      }
    
    }
    
    // Return the mutated data.
    return $data;
    
  }
  
  // Capture checkbox values within a data set.
  public static function checkbox( array $data, $checkboxes ) {
    
    // Convert strings to an array.
    $checkboxes = is_string($checkboxes) ? [$checkboxes] : $checkboxes;
      
    // Mutate the checkboxes one by one.
    foreach( $checkboxes as $checkbox ) { 
      
      // Mutate items within an array.
      if( strpos($checkbox, '@') !== false ) {
        
        // Get the keys.
        $keys = array_map(function($key) {
          
          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');
          
        }, explode('@', $checkbox));
        
        // Look for the array of objects.
        if( array_get($data, $keys[0], false) !== false ) {
          
          // Get the array of objects.
          $array = array_get($data, $keys[0]);
          
          // Mutate each object within the array.
          foreach( $array as $index => $object ) {
            
            // Mutate the object's checkbox keys.
            $array[$index] = self::checkbox($object, $keys[1]);
            
          }
          
          // Save the array.
          $data = array_set($data, $keys[0], $array);
          
        }
        
      }
      
      // Otherwise, mutate items within an object.
      else {
      
        // Look for the key within the data set.
        if( array_get($data, $checkbox, false) !== false ) { 

          // Get the checkbox item.
          $item = array_get($data, $checkbox); 

          // Determine if the checkbox has a value, and if so, extract it.
          if( array_get($item, 'value', false) ) {

            // Get the checkbox value(s).
            $value = array_get($item, 'value');

            // Convert boolean-like values to a booleans.
            if( !is_array($value) and Cast::isBool($value) ) $value = Cast::toBool($value);

            // Save the value(s) of the checkbox.
            $data = array_set($data, $checkbox, $value);

          }

          // Otherwise, unchecked checkboxes should have a false value by default.
          else $data = array_set($data, $checkbox, false);

        }
        
      }
    
    }
    
    // Return the mutated data.
    return $data;
    
  }
  
  // Capture radio button values within a data set.
  public static function radio( array $data, $radios ) {
    
    // Convert strings to an array.
    $radios = is_string($radios) ? [$radios] : $radios;
      
    // Mutate the radio buttons one by one.
    foreach( $radios as $radio ) { 
      
      // Mutate items within an array.
      if( strpos($radio, '@') !== false ) {
        
        // Get the keys.
        $keys = array_map(function($key) {
          
          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');
          
        }, explode('@', $radio));
        
        // Look for the array of objects.
        if( array_get($data, $keys[0], false) !== false ) {
          
          // Get the array of objects.
          $array = array_get($data, $keys[0]);
          
          // Mutate each object within the array.
          foreach( $array as $index => $object ) {
            
            // Mutate the object's radio keys.
            $array[$index] = self::radio($object, $keys[1]);
            
          }
          
          // Save the array.
          $data = array_set($data, $keys[0], $array);
          
        }
        
      }
      
      // Otherwise, mutate items within an object.
      else {
      
        // Look for the key within the data set.
        if( array_get($data, $radio, false) !== false ) { 

          // Get the radio item.
          $item = array_get($data, $radio); 

          // Convert empty radio buttons to a null value by default.
          if( !isset($item) or empty($item) ) $data = array_set($data, $radio, null);

        }
        
      }
    
    }
    
    // Return the mutated data.
    return $data;
    
  }
  
  // Convert timestamps to dates within a data set.
  public static function timestamp( array $data, $timestamps ) {
    
    // Convert strings to an array.
    $timestamps = is_string($timestamps) ? [$timestamps] : $timestamps;
    
    // Mutate the timestamps one by one.
    foreach( $timestamps as $timestamp ) {
      
      // Mutate items within an array.
      if( strpos($timestamp, '@') !== false ) {
        
        // Get the keys.
        $keys = array_map(function($key) {
          
          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');
          
        }, explode('@', $timestamp));
        
        // Look for the array of objects.
        if( array_get($data, $keys[0], false) !== false ) {
          
          // Get the array of objects.
          $array = array_get($data, $keys[0]);
          
          // Mutate each object within the array.
          foreach( $array as $index => $object ) {
            
            // Mutate the object's radio keys.
            $array[$index] = self::timestamp($object, $keys[1]);
            
          }
          
          // Save the array.
          $data = array_set($data, $keys[0], $array);
          
        }
        
      }
      
      // Otherwise, mutate items within an object.
      else {
      
        // Look for the key within the data set.
        if( array_get($data, $timestamp, false) !== false ) { 

          // Get the timestamp value.
          $value = array_get($data, $timestamp); 
          
          // Verify that the timestamp is valid.
          if( isset($value) and is_int($value) ) {
          
            // Since the CMS outputs UNIX timestamps, remove milliseconds from the timestamp.
            $value = (int) floor($value / 1000); 

            // Convert the timestamp to a datetime.
            $datetime = (new DateTime())->setTimestamp($value); 

            // Convert the datetime to a moment.
            $moment = Moment::fromDateTime($datetime);

            // Save the moment.
            $data = array_set($data, $timestamp, $moment, true);
            
          }

        }
        
      }
      
    }
    
    // Return the mutated data.
    return $data;
    
  }
  
  // Mutate HTML fields within the data set. [alias]
  public static function html( array $data, $html ) {
    
    return forward_static_call('Mutator::text', $data, $html);
    
  }
  
  // Mutate text fields within the data set.
  public static function text( array $data, $texts ) {
    
    // Convert strings to an array.
    $texts = is_string($texts) ? [$texts] : $texts;
      
    // Mutate the text one by one.
    foreach( $texts as $text ) { 
      
      // Mutate items within an array.
      if( strpos($text, '@') !== false ) {
        
        // Get the keys.
        $keys = array_map(function($key) {
          
          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');
          
        }, explode('@', $text));
        
        // Look for the array of objects.
        if( array_get($data, $keys[0], false) !== false ) {
          
          // Get the array of objects.
          $array = array_get($data, $keys[0]);
          
          // Mutate each object within the array.
          foreach( $array as $index => $object ) {
            
            // Mutate the object's text keys.
            $array[$index] = self::text($object, $keys[1]);
            
          }
          
          // Save the array.
          $data = array_set($data, $keys[0], $array);
          
        }
        
      }
      
      // Otherwise, mutate items within an object.
      else {
      
        // Look for the key within the data set.
        if( array_get($data, $text, false) !== false ) { 

          // Get the text item.
          $item = array_get($data, $text); 

          // Convert empty text to an empty string.
          if( !isset($item) or empty($item) ) $data = array_set($data, $text, "");

        }
        
      }
    
    }
    
    // Return the mutated data.
    return $data;
    
  }
  
  // Mutate the data by aliasing keys within the data set.
  public static function alias( array $data, array $aliases ) {
    
    // Alias keys as different keys.
    foreach( $aliases as $alias => $source ) {
      
      // Only try to alias keys that actually exists.
      if( array_get($data, $source, false) !== false ) {
      
        // Save the alias, but disallow overwriting of keys that already exist.
        $data = array_set($data, $alias, array_get($data, $source));
          
      }
      
    }
    
    // Return the mutated data.
    return $data;
    
  }
  
  // Mutate the data by renaming keys within the data set.
  public static function rename( array $data, array $renames ) {
    
    // Rename keys as different keys.
    foreach( $renames as $new => $old ) {
      
      // Only try to rename keys that actually exists.
      if( array_get($data, $old, false) !== false ) {
        
        // Prevent renaming if the new key already exists.
        if( array_get($data, $new, false) !== false ) continue;
      
        // Save the renamed key.
        $data = array_set($data, $new, array_get($data, $old));
        
        // Remove the old key.
        $data = array_unset($data, $old);
          
      }
      
    }
    
    // Return the mutated data.
    return $data;
    
  }
  
  // Mutate the data by replacing values within the data set.
  public static function replace( array $data, array $replaces ) {
    
    // Replace values with different values.
    foreach( $replaces as $key => $value ) {
      
      // Only try to replace keys that actually exists.
      if( array_get($data, $key, false) !== false ) {
      
        // Replace the key's existing value with the new value.
        $data = array_set($data, $key, $value, true);
          
      }
      
    }
    
    // Return the mutated data.
    return $data;
    
  }
  
  // Mutate the data by removing keys within the data set.
  public static function remove( array $data, $removes ) {
    
    // Convert strings to arrays.
    $removes = is_string($removes) ? [$removes] : $removes;
    
    // Remove keys with the data set.
    foreach( $removes as $remove ) {
      
      // Remove any keys that exist.
      $data = array_unset($data, $remove);
      
    }
    
    // Return the mutated data.
    return $data;
    
  }
  
  // Mutate the data by adding keys to the data set.
  public static function add( array $data, array $adds ) {

    // Add keys to the data set.
    foreach( $adds as $key => $value ) {
      
      // Add any keys that don't already exist.
      $data = array_set($data, $key, $value);
      
    }
    
    // Return the mutated data.
    return $data;
    
  }
  
}

?>