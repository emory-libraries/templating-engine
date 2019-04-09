<?php

function preg_replace_all( $pattern, $replacement, $subject ) {
  
  while( preg_match($pattern, $subject) ) {
    
    $subject = preg_replace($pattern, $replacement, $subject);
    
  }
  
  return $subject;
  
}

function is_regex( $string ) {
  
  return @preg_match($string, '') !== false;
  
}

?>