<?php

spl_autoload_register(function ( $class ) {
  
  $path = __DIR__.'/'.str_replace("\\", DIRECTORY_SEPARATOR, $class).'.php';
  
  if( file_exists($path) ) include_once($path);
  
});

// Initialize helpers.
$HELPERS = [];

return function() {
  
  // Use global helpers.
  global $HELPERS;

  // Look for helpers.
  $helpers = array_filter(scandir(__DIR__), function($helper) {

    return !in_array($helper, ['.', '..', 'autoload.php']) && !is_dir(__DIR__."/$helper");

  }); 
  
  // Load helpers.
  foreach( $helpers as $helper ) { 
    
    // Get the helper class.
    $class = basename($helper, '.php');
    
    // Extract helper methods.
    $methods = get_class_methods( new $class() );
    
    // Save helper methods.
    foreach( $methods as $method ) { $HELPERS[$method] = "$class::$method"; }

  }

  // Return.
  return $HELPERS;
  
};

?>