<?php

namespace CustomHelpers;

trait PassthroughHelpers {
  
  // Enable data to be passed throughed other helpers.
  public static function passthrough( array $data, $passthroughs, $recursive = true ) {
    
    // Set recursion if not set.
    $recursive = func_num_args() > 3 ? $recursive : true;
    
    // Initialize the result.
    $result = $data;
    
    // Enable passthrough of all items within a data set.
    if( $recursive ) {
      
      // Passthrough all values.
      $result = array_map(function($item) use ($passthroughs, $recursive) {
        
        // Send each data item through the passthrough helper.
        return CustomHelpers\PassthroughHelpers::passthrough($item, $passthroughs, $recursive);
        
      }, $data);
      
    }
    
    // Otherwise, enable passthrough of a single data item.
    else {
      
      // Get all helpers.
      $helpers = API::get('/helpers');
      
      // Initialize binding data.
      $bind = [];
      
      // Get binding data.
      foreach( $data as $key => $value ) { $bind[":$key"] = $value; }
      
      // Merge all passthrough data.
      foreach( $passthroughs as $key => $calls ) {
        
        // Pass the data through each of the helpers.
        foreach( $calls as $helper => $arguments ) {
          
          // Ignore invalid helpers.
          if( !isset($helpers[$helper]) ) continue;
          
          // Otherwise, bind arguments.
          $arguments = array_map(function($argument) use ($bind) {
            
            // Bind the argument, or use it as is if it cannot be bound.
            return ($bind[$argument] ?? $argument);
            
          }, $arguments);
          
          // Run the passthrough, and save the result.
          $result = array_set($result, $key, call_user_func_array($helpers[$helper], $arguments));
          
        }
        
      }
      
    }
    
    // Return the result.
    return $result;
    
  }
  
}

?>