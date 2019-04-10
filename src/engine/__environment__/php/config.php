<?php

// Configure the templating engine.
define('CONFIG', [
  
  // Stores information about the active site.
  '__site__' => [
    'domain' => DOMAIN,
    'site' => SITE,
    'environment' => environment
  ],
  
  // Configures document paths.
  'document' => [
    
    'root' => DOCUMENT_ROOT
    
  ],
  
  // Configures server paths.
  'server' => [
    
    'root' => SERVER_ROOT,
    'path' => SERVER_PATH
    
  ],
  
  // Configures site paths.
  'site' => [
    
    'root' => SITE_ROOT,
    'css' => SITE_ROOT.'/css',
    'js' => SITE_ROOT.'/js',
    'images' => SITE_ROOT.'/images',
    'icons' => SITE_ROOT.'/icons',
    'assets' => SITE_ROOT.'/assets',
    'fonts' => SITE_ROOT.'/fonts'
    
  ],
  
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
  
  // Configures patterns paths.
  'patterns' => [
    
    'root' => PATTERNS_ROOT
    
  ],
  
  // Configures engine paths.
  'engine' => [
    
    'root' => ENGINE_ROOT,
    'config' => ENGINE_ROOT.'/config',
    'env' => ENGINE_ROOT.'/.env',
    'php' => ENGINE_ROOT.'/php',
    'classes' => ENGINE_ROOT.'/php/classes',
    'helpers' => ENGINE_ROOT.'/php/helpers',
    'css' => ENGINE_ROOT.'/css',
    'js' => ENGINE_ROOT.'/js',
    'images' => ENGINE_ROOT.'/images',
    'icons' => ENGINE_ROOT.'/icons',
    'assets' => ENGINE_ROOT.'/assets',
    'fonts' => ENGINE_ROOT.'/fonts',
    'cache' => [
      
      'root' => CACHE_ROOT,
      
      // Specify a directory within the cache where data for partials will be stored.
      'partials' => CACHE_ROOT.'/partials',
      
      // Specify a directory within the cache where data for templates will stored.
      'templates' => CACHE_ROOT.'/templates',
      
      // Specify a file path within the cache where data for handlebars helpers will be stored. This file will be encoded as JSON.
      'helpers' => CACHE_ROOT.'/helpers.json'
    ]
    
  ],
  
  // Configures cache paths.
  // TODO: Change references to `cache` config to use `engine.cache`.
  'cache' => [
    'root' => CACHE_ROOT,
    "partials" => CACHE_ROOT.'/partials',
    "templates" => CACHE_ROOT.'/templates',
    "helpers" => CACHE_ROOT.'/.helpers.json'
  ],
  
  // Configures default file extensions for generated files.
  'ext' => [
    'template'  => '.hbs',
    'data'      => '.json',
    'cache'     => '.php'
  ],
  
  // Configures the handlebars engine.
  // TODO: Move `handlebars.helpers` config into `engine` config.
  // TODO: Remove `handlebars.templates` and `handlebars.partials` config and use dynamic directory listings via `scandir` instead.
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