<?php

// Load configurations.
require_once __DIR__."/index.config.php";

// Load environment variables.
(Dotenv\Dotenv::create(dirname(CONFIG['engine']['env']), basename(CONFIG['engine']['env'])))->load();

// Set the timezone.
date_default_timezone_set('America/New_York');

// Add benchmark point.
if( DEVELOPMENT ) Performance\Performance::point('Indexer initialized.');

?>