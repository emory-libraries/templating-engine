<?php

// Look for libraries.
$libraries = array_filter(scandir(__DIR__), function($library) {
  
  return !in_array($library, ['.', '..', '.DS_Store', '.DAV', '.acl', 'autoload.php']);
  
});

// Load libraries.
foreach( $libraries as $library ) { include __DIR__."/$library"; }

?>