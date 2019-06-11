<?php

// Autoload the helper classes.
spl_autoload_register(function ($class) {
  
  // Get the helper class' file path.
  $path = __DIR__.'/'.str_replace("\\", DIRECTORY_SEPARATOR, $class).'.php';
  
  // Include the helpers.
  if( file_exists($path) ) include_once($path);
  
});

?>