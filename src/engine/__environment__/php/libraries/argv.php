<?php

// Parse arguments passed in via the command line.
function parse_argv() {
  
  // Get arguments.
  $arguments = $_SERVER['argv'];
  
  // Remove the calling script from the arguments.
  array_shift($arguments);
  
  // Parse the remaining arguments.
  foreach( $arguments as $index => $argument ) {
    
    // Split the argument.
    $split = explode('=', $argument);
    
    // Extract the option and value if applicable.
    $option = str_starts_with($split[0], '-') ? $split[0] : false;
    $value = $option ? $split[1] ?? null : $split[0];
    
    // Save the named option if found.
    if( $option ) {
      
      // Remove options delimiters from the option.
      $option = preg_replace('/^--?/', '', $option);
    
      // Save the option with its value.
      $arguments[$option] = $value;
      
      // Unset the original index.
      unset($arguments[$index]);
      
    }
    
    
  }
  
  // Return the parsed arguments.
  return $arguments;
  
}

?>