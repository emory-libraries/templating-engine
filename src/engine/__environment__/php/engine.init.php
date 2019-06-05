<?php

// Load shutdown functions.
require_once ENGINE_ROOT."/php/error.php";

// Load dependencies.
require_once ENGINE_ROOT."/php/dependencies/autoload.php";

// Load libraries.
require_once ENGINE_ROOT."/php/libraries/autoload.php";

// Load classes.
require_once ENGINE_ROOT."/php/classes/autoload.php";
require_once ENGINE_ROOT."/php/helpers/autoload.php";

// Load configurations.
require_once ENGINE_ROOT."/php/engine.config.php";

// Load environment variables.
(Dotenv\Dotenv::create(dirname(CONFIG['engine']['env']), basename(CONFIG['engine']['env'])))->load();

// Set the timezone.
date_default_timezone_set('America/New_York');

// Add benchmark point.
if( BENCHMARKING ) Performance\Performance::point('Templating engine initialized.');

?>