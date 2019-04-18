<?php

// Initialize the templating engine.
require "init.php";

// Enable debugging during development.
if( $_SERVER['HTTP_HOST'] == 'localhost' or $_SERVER['SERVER_NAME'] == 'localhost' or ENVIRONMENT == 'development' ) {
  
  // Report all errors.
  error_reporting(E_ALL);
  
  // Display all errors.
  ini_set('display_errors', 1);
  
}

// Start the templating engine.
new Engine();

?>