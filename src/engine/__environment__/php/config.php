<?php

define('CONFIG', [
  
  // Configures data paths.
  'data' => [
    
    'environment' => [
      'root' => DATA_ROOT,
      'global' => DATA_ROOT.'/_global',
      'meta' => DATA_ROOT.'/_meta',
      'shared' => DATA_ROOT.'/_shared'
    ],
    
    'site' => [
      'root' => SITE_DATA,
      'global' => SITE_DATA.'/_global',
      'meta' => SITE_DATA.'/_meta',
      'shared' => SITE_DATA.'/_shared'
    ],
    
    'shared' => array_map(function($site) {
        
        return DATA_ROOT.'/'.$site.'/_shared';
        
      }, array_values(array_filter(scandir_clean(DATA_ROOT), function($site) {
        
        return $site !== SITE;
        
      })))
    
  ],
  
  // Configures cache paths.
  'cache' => [
    
    'root' => CACHE_ROOT,
    
    // Specify a directory within the cache where data for partials will be stored.
    "partials" => CACHE_ROOT.'/partials',

    // Specify a directory within the cache where data for templates will stored.
    "templates" => CACHE_ROOT.'/templates',

    // Specify a file path within the cache where data for handlebars helpers will be stored. This file will be encoded as JSON.
    "helpers" => CACHE_ROOT.'/.helpers.json'
    
  ],
  
  // Configures default file extensions for generated files.
  'ext' => [
    'template'  => '.hbs',
    'data'      => '.json',
    'cache'     => '.php'
  ],
  
  // Configures the handlebars engine.
  'handlebars' => [
  
    'templates' => PATTERNS_ROOT.'/'.'60-templates',
    'partials' => PATTERNS_ROOT,
    'helpers' => __DIR__.'/helpers'
    
  ],
  
  // Configures the markdown engine.
  'markdown' => [
    
    // Enables safe mode to prevent the use of HTML within markdown.
    'useSafeMode' => true,

    // Enables automatic header IDs by default.
    'enabledHeaderIds' => true,

    // Overwrite existing IDs when automatically generating header IDs.
    'overwriteHeaderIds' => true,

    // Sets default header level to start with the given value.
    'headerLevelStart' => 2,

    // Disables the use of images within markdown.
    'disableImages' => true
    
  ]
  
]);

?>