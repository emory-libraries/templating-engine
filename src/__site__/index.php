<?php

// Get data about the site.
$domain = basename(__DIR__);
$environment = explode('.', $domain)[0];

// Determine site environment.
switch($environment) {
    
  // Use the default environment keyword for `staging` and `qa` environments.
  case 'staging': 
  case 'qa':
    break;
    
  // Use the keyword `development` for `dev` environments.
  case 'dev': 
    $environment = 'development';
    break;
    
  // Otherwise, assume its a `production` environment.
  default:
    $environment = 'production';
    
}

// Set site globals.
define('DOMAIN', $domain);
define('ENVIRONMENT', $environment);
define('SITE', ($environment == 'production' ? $domain : preg_replace("/^{$environment}\./", '', $domain)));

// Set path globals.
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('SITE_ROOT', __DIR__);
define('SERVER_ROOT', dirname(__DIR__)); 
define('SERVER_PATH', str_replace(DOCUMENT_ROOT.'/', '', SERVER_ROOT));
define('DATA_ROOT', SERVER_ROOT.'/data/'.ENVIRONMENT);
define('PATTERNS_ROOT', SERVER_ROOT.'/patterns/'.ENVIRONMENT);
define('ENGINE_ROOT', SERVER_ROOT.'/engine/'.ENVIRONMENT);
define('CACHE_ROOT', SERVER_ROOT.'/engine/'.ENVIRONMENT.'/php/cache');
define('SITE_DATA', DATA_ROOT.'/'.SITE);
  
// Load the templating engine.
require ENGINE_ROOT."/php/engine.php";

?>