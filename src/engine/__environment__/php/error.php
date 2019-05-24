<?php

// Register shutdown function(s) for handling fatal errors.
register_shutdown_function(function() {
  
  // Get the last error.
  $error = error_get_last();
  
  // Get the requested endpoint.
  $endpoint = Request::endpoint();
  
  // If an error occurred, than force a custom 500 error page.
  if( isset($error) and $endpoint !== '/500' ) {
    
    // Get the endpoint for the 500 error page.
    $endpoint = API::get('/endpoint/500');
      
    // Then, render the 500 error page.
    echo Renderer::error($endpoint);
    
  }
  
});

?>