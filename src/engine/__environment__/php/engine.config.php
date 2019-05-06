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
define('CONFIG', [
  
  // Store some information about the current setup.
  'localhost' => LOCALHOST,
  'ngrok' => NGROK,
  'development' => DEVELOPMENT,
  
  // Stores information about the active site.
  '__site__' => [
    'domain' => DOMAIN,
    'site' => SITE,
    'environment' => ENVIRONMENT
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
    'root' => SITE_ROOT
  ],
  
  // Configures engine paths.
  'engine' => [
    'root' => ENGINE_ROOT,
    'config' => ENGINE_ROOT.'/config',
    'env' => ENGINE_ROOT.'/.env',
    'classes' => ENGINE_ROOT.'/php/classes',
    // TODO: Add helpers to index.
    'helpers' => ENGINE_ROOT.'/php/helpers',
    // TODO: Determine if icons and logos should be indexed.
    'icons' => ENGINE_ROOT.'/icons',
    'logos' => ENGINE_ROOT.'/logos',
    'cache' => [
      'root' => CACHE_ROOT,
      'pages' => CACHE_ROOT.'/pages/'.DOMAIN,
      'index' => CACHE_ROOT.'/index/'.DOMAIN,
      'tmp' => CACHE_ROOT.'/tmp/'.DOMAIN
    ]
  ],
  
  // Configures the handlebars engine.
  'handlebars' => [
    'flags' => [
      'FLAG_HANDLEBARSJS',
      'FLAG_THIS',
      'FLAG_ELSE',
      'FLAG_RUNTIMEPARTIAL',
      'FLAG_NAMEDARG',
      'FLAG_PARENT',
      'FLAG_ADVARNAME',
      'FLAG_JSLENGTH',
      'FLAG_SPVARS',
    ],
    'helpers' => (include ENGINE_ROOT.'/php/helpers/autoload.php')()
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
    
  ],
  
  // Configures default error messaging for when error data files and/or templates are missing.
  'errors' => [
    400 => [
      'status' => 'Bad Request',
      'message' => 'Your request could not be understood as is.'
    ],
    404 => [
      'status' => 'Not Found',
      'message' => 'The page you were looking for could not be found'
    ],
    500 => [
      'status' => 'Internal Server Error',
      'message' => 'The server encountered an internal error and could not complete your request.'
    ],
    515 => [
      'status' => 'Templating Engine Error',
      'message' => 'The templating engine encountered an error and could not fulfill your request.'
    ]
  ],
  
  // Configures defaults.
  'defaults' => [
  
    // Configures the default error template that's used when one does not exists.
    'errorTemplate' => <<<'ERROR_TEMPLATE'
    
<h1 class="heading -h1">{{code}}</h1>
<p class="text -lead">{{status}}</p>
<p class="text">{{message}}</p>

ERROR_TEMPLATE
    
  ],
  
  // Configures assets.
  'assets' => [
    
    // Sets the keep alive time for caching assets in the browser.
    'keepAlive' => Renderer::KEEP_ALIVE_MONTH
    
  ],
  
  // Get the contents of all templating engine configuration files.
  'config' => array_reduce(Index::scan(ENGINE_ROOT.'/config'), function($config, $file) {
    
    // Get file's extension and basename.
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    
    // Get the file's endpoint within the configuration folder.
    $endpoint = str_replace(ENGINE_ROOT.'/config/', '', $file);
    $endpoint = str_replace(".$ext", '', $endpoint);
    
    // Convert the file's endpoint into a usable array key.
    $key = str_replace('/', '.', $endpoint); 
    
    // Get the configuration file's contents.
    $contents = Transformer::transform(File::read($file), $ext);
    
    // Merge all configuration files into a single array.
    return array_set($config, $key, $contents);
    
  }, []),
  
  // Get available layouts.
  // TODO: Considering indexing layouts.
  'layouts' => array_reduce(Index::scan(ENGINE_ROOT.'/layout', false), function($layouts, $file) {
    
    // Get file's extension and basename.
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    
    // Get the file's endpoint within the configuration folder.
    $endpoint = str_replace(ENGINE_ROOT.'/layout/', '', $file);
    $endpoint = str_replace(".$ext", '', $endpoint);
    
    // Convert the file's endpoint into a usable array key.
    $key = str_replace('/', '.', $endpoint); 
    
    // Get the layout file's contents.
    $contents = File::read($file);
      
    // Continue reducing.
    return array_set($layouts, $key, $contents);
    
  }, []),
  
  // Get and load all icons.
  // TODO: Consider caching icons.
  'icons' => array_reduce(Index::scan(ENGINE_ROOT.'/icons/svg', false), function($icons, $icon) {
    
    // Get the icon's SVG content.
    $svg = File::read($icon);
    
    // Get the icon's ID.
    $id = File::id($icon);
    
    // Save the icon.
    $icons[$id] = $svg;
    
    // Continue merging all icons into a single array.
    return $icons;
    
  }, []),
  
  // Get and load all logos.
  // TODO: Consider caching logos.
  'logos' => array_reduce(Index::scan(ENGINE_ROOT.'/logos', false), function($logos, $logo) {
    
    // Get the icon's SVG content.
    $svg = File::read($logo);
    
    // Get the logo's ID.
    $id = File::id($logo);
    
    // Save the logo.
    $logos[$id] = $svg;
    
    // Continue merging all logos into a single array.
    return $logos;
    
  }, [])
  
]);

?>