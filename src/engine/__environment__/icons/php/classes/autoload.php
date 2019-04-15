<?php

// Autoload classes.
spl_autoload_register(function($class) {
  
  // Get the path to the class definition.
  $path = __DIR__."/{$class}.php";
  
  // Verify that the class exists, and load it.
  if( file_exists($path) ) include $path;
  
});

?>