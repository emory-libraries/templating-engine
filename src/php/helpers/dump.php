<?php

return function( ...$variables ) {
  
  // Get the second to last user arguments.
  $last = $variables[count($variables) - 2];
  
  // Exclude options unless explicitly included.
  if( isset($last) and $last !== true ) $options = array_pop($variables);
  
  // Dump the data.
  foreach( $variables as $variable ) { var_dump($variable); }
  
};

?>