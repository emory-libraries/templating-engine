<?php

// Load dependencies.
require "dependencies/autoload.php";

// Load libraries.
require "libraries/autoload.php";

// Load classes.
require "classes/autoload.php";

// Load configurations.
$config = new Config();

// Set timezone.
date_default_timezone_set('America/New_York');

?>