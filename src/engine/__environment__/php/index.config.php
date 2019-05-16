<?php

// Get pattern groups.
define('PATTERN_GROUPS', array_reduce(array_map(function($path) {

    // Return pattern folder data.
    return [
      'group' => preg_replace('/^\d{1,2}-/', '', basename($path)),
      'path' => $path
    ];

  }, Index::scan(PATTERNS_ROOT, false)), function($groups, $group) {

    // Merge the group data into a single array.
    $groups[$group['group']] = $group['path'];

    // Continue reducing.
    return $groups;

  }, []));

// Configure the templating engine.
define('CONFIG', array_merge((include ENGINE_ROOT.'/php/config.php'), [
  
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
    ],
    'shared' => array_map(function($path) {
      
      // Initialize the files.
      $files = [];
      
      // Get a list of all shared data files from the sibling site.
      if( file_exists($path) ) $files = array_map(function($file) use ($path) {
        
        // Append the original path to the file.
        return cleanpath("$path/$file");
        
      }, scandir_recursive($path));
      
      // Return the list of shared files.
      return $files;
      
    }, array_reduce(array_map(function($site) {
        
      // Capture the site and path to its shared folder.
      return [
        'site' => $site,
        'path' => DATA_ROOT.'/'.$site.'/_shared'
      ];

    }, array_values(array_filter(Index::scan(DATA_ROOT, false), function($folder) {
     
      // Filter out environment-level data folders, and only keep site-level folders.
      return !in_array($folder, ['_meta', '_global', '_shared']);
      
      
    }))), function($shared, $site) {

      // Merge the site-specific shared files into a single array.
      $shared[$site['site']] = $site['path'];

      // Continue reducing.
      return $shared;

    }, []))
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
  
  // Configures asset paths, and indicates if their assets can be found recursively.
  'assets' => [
    SITE_ROOT.'/css' => true,
    SITE_ROOT.'/js' => true,
    SITE_ROOT.'/php' => false,
    SITE_ROOT.'/images' => true,
    SITE_ROOT.'/assets' => true,
    SITE_ROOT.'/documents' => true,
    SITE_ROOT.'/fonts' => true,
    SITE_DATA.'/images' => true,
    SITE_DATA.'/assets' => true,
    SITE_DATA.'/documents' => true,
    SITE_DATA => true,
    ENGINE_ROOT.'/css' => true,
    ENGINE_ROOT.'/js' => true,
    ENGINE_ROOT.'/php/scripts' => false,
    ENGINE_ROOT.'/images' => true,
    ENGINE_ROOT.'/assets' => true,
    ENGINE_ROOT.'/documents' => true,
    ENGINE_ROOT.'/fonts' => true,
    ENGINE_ROOT.'/icons/php' => false
  ],
  
  // Get the contents of all templating engine meta files.
  'meta' => scandir_recursive(ENGINE_ROOT.'/meta', ENGINE_ROOT.'/meta')
  
]));

?>