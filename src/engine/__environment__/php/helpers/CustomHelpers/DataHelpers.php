<?php

namespace CustomHelpers;

use Cast;

trait DataHelpers {

  // Lookup all data files in the same directory.
  
  
  //************* META *************//
  
  // Get all site-specific meta data.
  public static function getMetaData() {
    
    // Lookup and return all site data.
    return forward_static_call('CustomHelpers\DataHelpers::getData', 'meta');
    
  }
  
  // Get site-specific meta data by path.
  public static function getMetaDataByPath( string $path, array $options ) {
  
    // Query the site's meta data for the given path.
    return forward_static_call('CustomHelpers\DataHelpers::getDataByPath', $path, 'meta', $options);
  
  }

  
  //************* GLOBAL *************//
  
  // Get all site-specific global data.
  public static function getGlobalData() {
    
    // Lookup and return all site data.
    return forward_static_call('CustomHelpers\DataHelpers::getData', 'global');
    
  }
  
  // Get site-specific global data by path.
  public static function getGlobalDataByPath( string $path, array $options ) {
  
    // Query the site's global data for the given path.
    return forward_static_call('CustomHelpers\DataHelpers::getDataByPath', $path, 'global', $options);
  
  }

  
  //************* SHARED *************//
  
  // Get all site-specific shared data.
  public static function getSharedData() {
    
    // Lookup and return all site data.
    return forward_static_call('CustomHelpers\DataHelpers::getData', 'shared');
    
  }
  
  // Get site-specific shared data by path.
  public static function getSharedDataByPath( string $path, array $options ) {
  
    // Query the site's shared data for the given path.
    return forward_static_call('CustomHelpers\DataHelpers::getDataByPath', $path, 'shared', $options);
  
  }

  
  //************* SITE *************//
  
  // Get all site-specific data.
  public static function getSiteData() {
    
    // Lookup and return all site data.
    return forward_static_call('CustomHelpers\DataHelpers::getData', 'site');
    
  }
  
  // Get site-specific data by path.
  public static function getSiteDataByPath( string $path, array $options ) {
  
    // Query the site's data for the given path.
    return forward_static_call('CustomHelpers\DataHelpers::getDataByPath', $path, 'site', $options);
  
  }
  
  // Get site-specific data of a given page type.
  public static function getSiteDataOfType( string $type ) {
  
    // Query the site's index to find all data of a given page type.
    return forward_static_call('CustomHelpers\DataHelpers::getDataOfType', $type);
  
  }
  
  // Get site-specific data of a given page type by path.
  public static function getSiteDataByPathOfType( string $path, string $type, $options = [] ) { 
  
    // Query the site's index to find all data of a given page type at the designated path.
    return forward_static_call('CustomHelpers\DataHelpers::getDataOfType', $path, $type, $options);
  
  }
  
  //************* UNIVERSAL *************//
  
  // Get all index data.
  public static function getData( $target = 'site' ) {
    
    // Get the index target, or use site-level data by default. 
    $target = is_string($target) ? $target : 'site';
    
    // Get the target index.
    $index = array_get(SITE_DATA_INDEX, $target, []);
    
    // Return all data.
    return ($target == 'site' ? data_flatten(data_extract($index)) : $index);
    
  }
  
  // Get index data by a path.
  public static function getDataByPath( string $path, $target = 'site', $options = [] ) {
    
    // Capture options.
    $options = func_num_args() == 3 ? $options : array_last(func_get_args());
    
    // Get the index target, or use site-level data by default. 
    $target = func_num_args() == 3 ? $target : 'site';
    
    // Initialize the result.
    $result = [];
    
    // Determine if the lookup should be recursive.
    $recursive = Cast::toBool(array_get($options, 'hash.recursive', false));
    
    // Get the target index.
    $index = array_get(SITE_DATA_INDEX, $target);
    
    // Convert the given path to a key.
    $key = str_replace('/', '.', trim($path, '/'));
    
    // Lookup the key within the index.
    $files = array_get($index, $key, []);
    
    // If a data file was given rather than a directory, then just return it.
    if( isset($files['path']) ) $result = [$files];
    
    // Otherwise, return all data files within a directory.
    else $result = $recursive ? $files : array_filter($files, function($file) {

      // If recursion is not enabled, then only extract the first level of files.
      return isset($file['path']);
      
    });
      
    // Return the data for the files.
    return ($target == 'site' ? data_flatten(data_extract($result)) : array_get($result, $key));
    
  }
  
  // Get index data of a given page type.
  public static function getDataOfType( string $type ) { 
    
    // Get the target index.
    $index = array_get(SITE_DATA_INDEX, 'site', []);
    
    // If a template ID was given instead of a page type, then get the page type based on the template ID.
    if( array_get(CONFIG, "config.template.$type", false) !== false ) $type = array_get(CONFIG, "config.template.$type");
    
    // Otherwise, assume a page type was given, and find all files with that type.
    return data_flatten(data_extract(data_of_page_type($index, $type)));

  }
  
  // Get index data of a given page type by path.
  public static function getDataByPathOfType( string $path, string $type, $options = [] ) {
  
    // Get the data files at the given path.
    $files = forward_static_call('CustomHelpers\DataHelpers::getDataByPath', $path, 'site', $options);
    
    // If a template ID was given instead of a page type, then get the page type based on the template ID.
    if( array_get(CONFIG, "config.template.$type", false) !== false ) $type = array_get(CONFIG, "config.template.$type");
    
    // Filter the files for the given page type.
    return array_filter($files, function($file) use ($type) {
      
      // Only keep files that use the given page type.
      return array_get($file, 'data.template') == $type;
      
    });
    
  }
  
}

?>