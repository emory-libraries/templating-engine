<?php

// Configure the templating engine.
define('CONFIG', array_merge((include ENGINE_ROOT.'/php/config.global.php'), (include ENGINE_ROOT.'/php/config.index.php'), [
  
  // Capture data about the setup.
  'development' => DEVELOPMENT,
  
  // Capture data about the site in question.
  '__site__' => [
    'environment' => ENVIRONMENT,
    'domain' => DOMAIN,
    'site' => SITE,
  ],
  
  // Configure document paths.
  'document' => [
    'root' => DOCUMENT_ROOT
  ],
  
  // Configure server paths.
  'server' => [
    'root' => SERVER_ROOT,
    'path' => SERVER_PATH
  ],
  
  // Configures site paths.
  'site' => [
    'root' => SITE_ROOT,
    'css' => SITE_ROOT.'/css',
    'js' => SITE_ROOT.'/js',
    'php' => SITE_ROOT.'/php',
    'images' => SITE_ROOT.'/images',
    'assets' => SITE_ROOT.'/assets',
    'documents' => SITE_ROOT.'/documents',
    'fonts' => SITE_ROOT.'/fonts',
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
    ]
  ],
  
  // Configures patterns paths.
  'patterns' => [
    'root' => PATTERNS_ROOT,
    'groups' => PATTERN_GROUPS
  ],
  
  // Configures engine paths.
  'engine' => [
    'root' => ENGINE_ROOT,
    'env' => ENGINE_ROOT.'/.env',
    'meta' => ENGINE_ROOT.'/meta',
    'config' => ENGINE_ROOT.'/config',
    'php' => ENGINE_ROOT.'/php',
    'scripts' => ENGINE_ROOT.'/php/scripts',
    'css' => ENGINE_ROOT.'/css',
    'js' => ENGINE_ROOT.'/js',
    'images' => ENGINE_ROOT.'/images',
    'icons' => ENGINE_ROOT.'/icons',
    'logos' => ENGINE_ROOT.'/logos',
    'assets' => ENGINE_ROOT.'/assets',
    'documents' => ENGINE_ROOT.'/documents',
    'fonts' => ENGINE_ROOT.'/fonts',
    'cache' => [
      'root' => CACHE_ROOT,
      'pages' => CACHE_ROOT.'/pages/'.DOMAIN,
      'index' => CACHE_ROOT.'/index/'.DOMAIN,
      'tmp' => CACHE_ROOT.'/tmp/'.DOMAIN
    ]
  ],
  
]));

?>