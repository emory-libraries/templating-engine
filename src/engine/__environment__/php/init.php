<?php

// Load dependencies.
require_once "dependencies/autoload.php";

// Load libraries.
require_once "libraries/autoload.php";

// Load classes.
require_once "classes/autoload.php";

// Load configurations.
require_once "config.php";

// Load environment variables.
(Dotenv\Dotenv::create(dirname(__DIR__)))->load();

// Set the timezone.
date_default_timezone_set('America/New_York');

?>