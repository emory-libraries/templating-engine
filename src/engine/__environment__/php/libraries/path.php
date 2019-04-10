<?php

// Strip erraneous slashes from a path string.
function cleanpath( $unclean ) {
  
  return preg_replace('/\/+/', '/', preg_replace('/\.\//', '', $unclean));
  
}

?>