<?php

// Configure the templating engine.
define('CONFIG', array_merge([
  
  // Store some information about the current setup.
  'localhost' => LOCALHOST,
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
    
    'root' => SITE_ROOT,
    'css' => SITE_ROOT.'/css',
    'js' => SITE_ROOT.'/js',
    'scripts' => SITE_ROOT.'/php/scripts',
    'images' => SITE_ROOT.'/images',
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

    }, array_values(array_filter(scandir_clean(DATA_ROOT), function($folder) {
     
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
    
    'groups' => array_reduce(array_map(function($folder) {
      
      // Get the atomic group name.
      $group = preg_replace('/^\d{1,2}-/', '', $folder);
      
      // Return folder data.
      return [
        'group' => $group,
        'path' => cleanpath(PATTERNS_ROOT.'/'.$folder)
      ];
      
    }, scandir_clean(PATTERNS_ROOT)), function($groups, $group) {

      // Merge the group data into a single array.
      $groups[$group['group']] = $group['path'];
      
      // Continue reducing.
      return $groups;
      
    }, [])
    
  ],
  
  // Configures engine paths.
  'engine' => [
    
    'root' => ENGINE_ROOT,
    'config' => ENGINE_ROOT.'/config',
    'env' => ENGINE_ROOT.'/.env',
    'meta' => ENGINE_ROOT.'/meta',
    'php' => ENGINE_ROOT.'/php',
    'classes' => ENGINE_ROOT.'/php/classes',
    'helpers' => ENGINE_ROOT.'/php/helpers',
    'scripts' => ENGINE_ROOT.'/php/scripts',
    'css' => ENGINE_ROOT.'/css',
    'js' => ENGINE_ROOT.'/js',
    'images' => ENGINE_ROOT.'/images',
    'icons' => ENGINE_ROOT.'/icons',
    'logos' => ENGINE_ROOT.'/logos',
    'assets' => ENGINE_ROOT.'/assets',
    'fonts' => ENGINE_ROOT.'/fonts',
    'cache' => CACHE_ROOT
    
  ],
  
  // Configures default file extensions for generated files.
  'ext' => [
    'template'  => '.hbs',
    'data'      => '.json',
    'cache'     => '.php'
  ],
  
  // Configures the handlebars engine.
  'handlebars' => [
  
    'partials' => array_reduce(array_map(function($partial) {
    
      // Get the full path of the partial.
      $path = PATTERNS_ROOT."/$partial";

      // Treat the partial as a template in order to get needed data out of it.
      $partial = new Template($path);

      // Get the partial's contents and recognized include names.
      return [
        'contents' => $partial->template,
        'plid' => $partial->plid,
        'id' => $partial->id,
        'path' => $partial->path
      ];

    }, scandir_recursive(PATTERNS_ROOT)), function($partials, $partial) {
      
      // Get all of the partial's parts.
      $plid = $partial['plid'];
      $id = $partial['id'];
      $path = $partial['path'];
      $contents = $partial['contents'];

      // Register the partial under its recognized PLID include name.
      $partials[$plid] = $contents;
      
      // Also register any alias include names.
      $partials[$path] = $partials[$id] = &$partials[$plid];

      // Continune reducing all partials into a single-level array.
      return $partials;

    }, []),
    'helpers' => (include ENGINE_ROOT.'/php/helpers/autoload.php')(),
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
    ]
    
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
  
  // Sets defaults for things.
  'defaults' => [
    
    // Set the default template when a template cannot be found for a route.
    'template' => 'templates-info'
    
  ]
  
], [
  
  // Get the contents of all templating engine configuration files.
  'config' => array_reduce(scandir_recursive(ENGINE_ROOT.'/config'), function($config, $file) {
    
    // Get file parts.
    $dirname = dirname($file);
    $basename = basename($file, '.'.pathinfo($file, PATHINFO_EXTENSION));
    
    // Get the file's endpoint.
    $endpoint = (isset($dirname) ? "$dirname/" : '').$basename;
    
    // Get the configuration file's key.
    $key = str_replace('/', '.', trim($endpoint, '/'));
    
    // Get the configuration file's contents.
    $contents = json_decode(File::read(ENGINE_ROOT."/config/".$file), true);
    
    // Read the configuration files into an array while keeping the configuration file structure. 
    return array_set($config, $key, $contents);
    
  }, []),
  
  // Get the contents of all templating engine meta files.
  'meta' => scandir_recursive(ENGINE_ROOT.'/meta', ENGINE_ROOT.'/meta'),
  
  // Get available layouts.
  'layouts' => array_reduce(scandir_clean(ENGINE_ROOT.'/layout'), function($layouts, $layout) {
    
    // Get the file's basename and contents.
    $basename = basename($layout, '.'.pathinfo($layout, PATHINFO_EXTENSION));
    $contents = file_get_contents(ENGINE_ROOT."/layout/$layout");
    
    // Read and save the layouts.
    $layouts[$basename] = $contents;
      
    // Continue reducing.
    return $layouts;
    
  }, []),
  
  // Get and load all icons.
  'icons' => array_reduce(scandir_clean(ENGINE_ROOT.'/icons/svg'), function($icons, $icon) {
    
    // Get the icon's SVG content.
    $svg = File::read(ENGINE_ROOT."/icons/svg/$icon");
    
    // Get the icon's ID.
    $id = File::id($icon);
    
    // Save the icon.
    $icons[$id] = $svg;
    
    // Continue merging all icons into a single array.
    return $icons;
    
  }, []),
  
  // Get and load all logos.
  'logos' => array_reduce(scandir_clean(ENGINE_ROOT.'/logos'), function($logos, $logo) {
    
    // Get the icon's SVG content.
    $svg = File::read(ENGINE_ROOT."/logos/$logo");
    
    // Get the logo's ID.
    $id = File::id($logo);
    
    // Save the logo.
    $logos[$id] = $svg;
    
    // Continue merging all logos into a single array.
    return $logos;
    
  }, [])
  
]));

?>