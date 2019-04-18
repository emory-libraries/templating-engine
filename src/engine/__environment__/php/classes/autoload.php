<?php

spl_autoload_register(function($class) {
  
  // Parse the file path.
  $path = __DIR__.'/'.str_replace("\\", DIRECTORY_SEPARATOR, $class).'.php';
  
  // Verify the file.
  if( file_exists($path) ) include $path;
  
});

?>