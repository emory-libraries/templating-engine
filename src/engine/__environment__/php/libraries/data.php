<?php

// Recursively extract the value of the data key for each file within a set of files.
function data_extract( array $files ) {

  // Recursively extract the data key from the files.
  return array_map(function($file) { 

    // Extract the data key.
    return (isset($file['data']) ? $file['data'] : data_extract($file));

  }, $files);

}

// Recursively lookup page types for each file within a set of files.
function data_of_page_type( array $files, string $type ) {

  // Recursively lookup files that match the page type.
  return array_filter($files, function($file) use ($type) {

    // Filter out files that don't match the page type.
    return (isset($file['data']) ? array_get($file, 'data.data.template') == $type : data_of_page_type($file, $type));

  });

}

// Recursively flatten a multidimensional array of data to a single level.
function data_flatten( array $files, $delimiter = '/', $parent = null ) {

  // Initialize the results.
  $result = [];

  // Get the prefix.
  $prefix = (isset($parent) and $parent !== '') ? $parent.$delimiter : '';

  // Flatten all files to a single array level.
  foreach( $files as $key => $file ) {

    // Capture files, and save them.
    if( isset($file['path']) ) $result[$prefix.$key] = $file;

    // Otherwise, assume it's an array and continue to flatten it.
    else $result = array_merge($result, data_flatten($file, $delimiter, $prefix.$key));

  }

  // Return the result.
  return $result;

}

?>