<?php

// Initialize configurations specific to the indexing process.
return [
  
  // Configure shared data.
  'data' => [
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
  
  // Get the contents of all templating engine meta files.
  'meta' => scandir_recursive(ENGINE_ROOT.'/meta', ENGINE_ROOT.'/meta')
  
];

?>