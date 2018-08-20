<?php

function fallback( ...$values ) {
  
  foreach( $values as $value ) {
    
    if( isset($value) ) return $value;
    
  }
  
  return $values[count($values) - 1];
  
}

?>