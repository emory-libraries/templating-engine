<?php

// Register an error handler for handling failures.
set_exception_handler(function($exception) {
  
  // Only handle templating engine failures.
  if( is_a($exception, 'Failure') ) echo $exception->getErrorPage();
  
  // For all other exceptions, return the error message.
  else echo $exception->getMessage().'<br>'.$exception->getTraceAsString();
  
});

?>