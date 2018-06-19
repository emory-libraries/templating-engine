<?php

function cleanpath( $unclean ) {
  
  return preg_replace('/\/+/', '/', preg_replace('/\.\//', '', $unclean));
  
}

?>