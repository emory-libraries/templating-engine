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
      'message' => 'Your request could not be understood as is.'
    ],
    404 => [
      'status' => 'Not Found',
      'message' => 'The page you were looking for could not be found',
      'template' => 'templates-error-404'
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
  
  /* This configures defaults for certain things throughout the templating engine.
   */
  'defaults' => [
  
    /* This defines the default error template that should be used when an existing template
     * pattern has not been defined and/or created for the intended error page.
     */
    'errorTemplate' => "
      <h1 class=\"heading -h1\">{{code}}</h1>
      <p class=\"text -lead\">{{status}}</p>
      <p class=\"text\">{{message}}</p>
    ",
    
    'layoutTemplate' => "
      <!doctype html>
      <html>
      <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" http-equiv=\"\">
        <title>{{title}}</title>
        <link rel=\"stylesheet\" href=\"{{baseUrl}}/css/style.min.css\">
      </head>
      <body>
        <div id=\"eul-vue\">{{template}}</div>
        <script src=\"{{baseUrl}}/js/bundle.min.js\"></script>
      </body>
      </html>
    "
    
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
    
  }, []),
  
];

?>