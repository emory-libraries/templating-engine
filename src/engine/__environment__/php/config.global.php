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

// Set base configurations for use by both the templating engine's renderer and indexer.
return [
  
  /* This configures default error messaging for erorr pages. This is mostly used for building
   * the correct HTTP response headers for error pages but is also used in instances where an
   * error page may not have an associated data file and/or template pattern. 
   */
  'errors' => [
    400 => [
      'status' => 'Bad Request',
      'message' => "Your request could not be understood as is."
    ],
    404 => [
      'status' => 'Not Found',
      'message' => "The page you were looking for could not be found",
      'template' => 'templates-error-404'
    ],
    500 => [
      'status' => 'Internal Server Error',
      'message' => "The server encountered an internal error and could not complete your request."
    ],
    514 => [
      'status' => 'Unknown or Invalid Index',
      'message' => "The templating engine encountered an error while trying to use data from the index."
    ],
    515 => [
      'status' => 'Nonexistent Page Template',
      'message' => "The templating engine encountered an error while trying to render the page's template."
    ],
    520 => [
      'status' => 'Failed to Compile Page',
      'message' => "The templating engine encountered an error while trying to compile the requested page."
    ],
    521 => [
      'status' => 'Failed to Render Page',
      'message' => "The templating engine encountered an error while trying to render the requested page."
    ]
  ],
  
  /* This configures defaults for certain things throughout the templating engine.
   */
  'defaults' => [
  
    /* This defines the default error template that should be used when an existing template
     * pattern has not been defined and/or created for the intended error page.
     */
    'errorTemplate' => "
      <h1 class=\"heading -h1\">{{code}}</h1>
      <p class=\"text -lead\">{{status}}</p>
      <p class=\"text\">{{{message}}}</p>
    ",
    
    'layoutTemplate' => "
      <!doctype html>
      <html>
      <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" http-equiv=\"\">
        <title>{{title}}</title>
        <style>".file_get_contents(ENGINE_ROOT.'/css/style.min.css')."</style>
      </head>
      <body>
        <div id=\"eul-vue\">{{template}}</div>
        <script>".file_get_contents(ENGINE_ROOT.'/js/bundle.min.js')."</script>
      </body>
      </html>
    ",
    
    "traceTemplate" => "
      <table class=\"table\" v-pre>
        <thead class=\"table-header\">
          <tr class=\"table-row\">
            <th class=\"table-cell -heading\">{{{trace.message}}}<th>
          </tr>
        </thead>
        <tbody class=\"table-body\">
          <tr class=\"table-row\">
            <td class=\"table-cell\"><pre>{{trace.stack}}</pre></td>
          </tr>
        </tbody>
      </table>
    "
    
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
    DATA_ROOT.'/_shared' => true,
    DATA_ROOT.'/_global' => true,
    ENGINE_ROOT.'/css' => true,
    ENGINE_ROOT.'/js' => true,
    ENGINE_ROOT.'/php/scripts' => false,
    ENGINE_ROOT.'/images' => true,
    ENGINE_ROOT.'/assets' => true,
    ENGINE_ROOT.'/documents' => true,
    ENGINE_ROOT.'/fonts' => true,
    ENGINE_ROOT.'/icons/php' => false
  ],
  
  /* This gets the contents of all templating engine configuration files, and reads
   * them into a single array, where the configuration folder's data structure is 
   * maintained.
   */
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
    
  }, [])
  
];

?>