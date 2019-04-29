<?php

/*
 * Transformer
 *
 * Transforms the file contents of a data file into a
 * user-friendly associative array based on the data
 * file's extension.
 */
class Transformer {
  
  // Defines recognized file extensions and their respective transformer methods.
  public static $transformers = [
    'transformJSON'   => ['json'],
    'transformYAML'   => ['yaml', 'yml'],
    'transformXML'    => ['xml']
  ];
  
  // Transform some file contents based on the given file extension.
  public static function transform( $contents, $ext ) {
    
    // Determine the transformer method to be used based on the file extension.
    foreach( self::$transformers as $method => $exts ) {
      
      // Pass the file contents to the appropriate transformer method.
      if( in_array($ext, $exts) ) return forward_static_call("Transformer::{$method}", $contents);
      
    }
    
  }
  
  // Transform some JSON file contents.
  public static function transformJSON( $contents ) {
    
    // Decode the JSON data into an associative array.
    return json_decode($contents, true);
    
  }
  
  // Transform some YAML file contents.
  public static function transformYAML( $contents ) {
    
    // Parse the YAML data into an associative array.
    return Yaml::parse($data);
    
  }
  
  // Transform some XML file contents.
  public static function transformXML( $contents ) {
    
    // Retrieve the XML data model.
    $model = self::transformJSON(file_get_contents(CONFIG['engine']['config'].'/xml.json'));
    
    // Determine the XML fields that contain HTML and should be escaped.
    $escape = array_get($model, 'config.html', []);
    
    // Convert the XML data to an associative array.
    $data = XML::toArray($contents, $escape);
    
    // Translate the XML data based on the XML data model.
    $data = Translator::translate($data, $model, 'xml');
    
    // Return the transformed XML data.
    return $data;
    
  }
  
}

?>