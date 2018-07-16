<?php

return function( ...$message ) {
  
  // Get the second to last user arguments.
  $last = $message[count($message) - 2];
  
  // Exclude options unless explicitly included.
  if( isset($last) and $last !== true ) $options = array_pop($message);
  
  // Output the log.
  return "<script>console.log(".implode(', ', array_map('json_encode', $message)).");</script>";
  
};

?>