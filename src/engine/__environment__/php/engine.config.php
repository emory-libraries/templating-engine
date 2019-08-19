<?php

// Configure the templating engine.
define('CONFIG', array_merge((include ENGINE_ROOT.'/php/config.global.php'), [

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
    'meta' => ENGINE_ROOT.'/meta',
    'php' => ENGINE_ROOT.'/php',
    // TODO: Add helpers to index.
    'helpers' => ENGINE_ROOT.'/php/helpers',
    // TODO: Determine if icons and logos should be indexed.
    'icons' => ENGINE_ROOT.'/icons',
    'logos' => ENGINE_ROOT.'/logos',
    'cache' => [
      'root' => CACHE_ROOT,
      'pages' => CACHE_ROOT.'/pages/'.DOMAIN,
      'index' => CACHE_ROOT.'/index/'.DOMAIN,
      'tmp' => CACHE_ROOT.'/tmp/'.DOMAIN,
      'cache' => CACHE_ROOT.'/'.DOMAIN
    ]
  ],

  // Configures the handlebars engine.
  'handlebars' => [
    'flags' => array_merge([
      'FLAG_HANDLEBARSJS',
      'FLAG_THIS',
      'FLAG_ELSE',
      'FLAG_RUNTIMEPARTIAL',
      'FLAG_NAMEDARG',
      'FLAG_PARENT',
      'FLAG_ADVARNAME',
      'FLAG_JSLENGTH',
      'FLAG_SPVARS',
      'FLAG_RAWBLOCK'
    ], (DEVELOPMENT ? [
      'FLAG_ERROR_LOG',
      'FLAG_ERROR_EXCEPTION'
    ] : []))
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

  // Configures asset headers.
  'assetHeaders' => [

    // Sets the keep alive time for caching assets in the browser.
    'keepAlive' => Renderer::KEEP_ALIVE_MONTH

  ],

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
    $id = strtolower(Path::filename($icon));

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
    $id = strtolower(Path::filename($logo));

    // Save the logo.
    $logos[$id] = $svg;

    // Continue merging all logos into a single array.
    return $logos;

  }, [])

]));

?>
