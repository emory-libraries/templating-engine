<?php

// Autoload all helper classes.
include_once __DIR__.'/autoload.php';

// Index all handlebars helpers.
return function() {
  
  // Initialize the result.
  $result = [];
  
  // Define a blacklist of files that should not be recognized as helpers.
  $blacklist = ['autoload.php', 'index.php'];

  // Look for helpers.
  $helpers = array_filter(scandir_clean(__DIR__), function($helper) use ($blacklist) {

    // Filter out any blacklisted files and directories..
    return !in_array(basename($helper), $blacklist) && !is_dir(__DIR__."/$helper");

  }); 
  
  // Load helpers.
  foreach( $helpers as $helper ) { 
    
    // Get the helper class' name.
    $class = basename($helper, '.php');
    
    // Extract the helper class' methods.
    $methods = get_class_methods($class);
    
    // Save the helper methods.
    foreach( $methods as $method ) { $result[$method] = "$class::$method"; }

  }

  // Return the result.
  return $result;
  
};

?>