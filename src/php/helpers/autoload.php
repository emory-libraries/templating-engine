<?php

return function() {

  // Initialize result.
  $result = [];

  // Look for helpers.
  $helpers = array_filter(scandir(__DIR__), function($helper) {

    return !in_array($helper, ['.', '..', 'autoload.php']);

  }); 
  
  // Load filters.
  foreach( $helpers as $helper ) { 

    $result[basename($helper, '.php')] = (include __DIR__."/$helper"); 

  }

  // Return.
  return $result;
  
};

?>