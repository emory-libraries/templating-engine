<?php

/*
 * Template
 *
 * Reads and parses a template file given a file path.
 */
class Template {
  
  // The path of the template file.
  public $file;
  
  // The path of the template file within Pattern Library.
  public $path;
  
  // The ID of the template file.
  public $id;
  
  // The PLID (Pattern Lab ID) of the template file.
  public $plid;
  
  // The name of the template.
  public $name;
  
  // The contents of the template file.
  public $template = '';
  
  // Defines the known flags that can be used to generate a regex pattern.
  const REGEX_GROUP_SUBGROUP_PATTERN = 1;
  const REGEX_GROUP_PATTERN = 2;
  const REGEX_PATTERN = 4;
  const REGEX_ANY = 8;
  
  // Constructs the data.
  function __construct( $path ) {
    
    // Extract template data when given a valid path.
    if( is_string($path) ) {
    
      // Save the template file.
      $this->file = $path;

      // Read the template file at the given path.
      $this->template = File::read($path);

      // Get the template file's ID, name, plid, and include path.
      $this->id = self::id($path);
      $this->name = self::name($path);
      $this->plid = self::plid($path);
      $this->path = self::path($path);
      
    }
    
    // Otherwise, capture the given template data when given an array.
    else if( is_array($path) ) {
      
      // Capture the template properties.
      $this->file = array_get($path, 'file');
      $this->path = array_get($path, 'path');
      $this->id = array_get($path, 'id');
      $this->plid = array_get($path, 'plid');
      $this->name = array_get($path, 'name');
      $this->template = array_get($path, 'template', '');
      
    }
    
  }
  
  // Builds a regex that can be used to parse data within a template's path based on the given flag.
  public static function regex( $flag = self::REGEX_ANY ) {
    
    // Get the recognized pattern groups.
    $groups = array_keys(PATTERN_GROUPS);
    
    // Initialize regex parts.
    $group = '(?:(?:(?P<groupNo>\d{1,2})-)?(?P<groupName>'.implode('|', $groups).'(?=-)))';
    $subgroup = '(?:\-(?:(?P<subgroupNo>\d{1,2})-)?(?P<subgroupName>[a-z-]+?(?=-)))';
    $pattern = '(?:-?(?:(?P<patternNo>\d{1,2})\-)?(?P<patternName>[a-z0-9\~\-\_]+))';
    
    // Build the regexs.
    $REGEX_GROUP_SUBGROUP_PATTERN = "/^{$group}{$subgroup}{$pattern}$/";
    $REGEX_GROUP_PATTERN = "/^{$group}{$pattern}$/";
    $REGEX_PATTERN = "/^{$pattern}$/";
    $REGEX_ANY = "/^(?J)(?:{$group}{$subgroup}{$pattern})|(?:{$group}{$pattern})|(?:{$pattern})$/";
    
    // Return the right regex based on the given flag.
    if( $flag & self::REGEX_GROUP_SUBGROUP_PATTERN ) return $REGEX_GROUP_SUBGROUP_PATTERN;
    if( $flag & self::REGEX_GROUP_PATTERN ) return $REGEX_GROUP_PATTERN;
    if( $flag & self::REGEX_PATTERN ) return $REGEX_PATTERN;
    return $REGEX_ANY;
    
  }
  
  // Parse some data about a template given a template file's path.
  public static function parse( $path ) {
    
    // Initialize the result.
    $result = [
      'group' => [
        'name'=> null,
        'number' => null
      ],
      'subgroup' => [
        'name' => null,
        'number' => null
      ],
      'pattern' => [
        'name' => null,
        'number' => null
      ]
    ];
    
    // Get the file's ID and directory name.
    $id = self::id($path);
    $dir = basename(dirname($path));
    
    // Attempt to parse template data using regexes.
    preg_match(self::regex(), $id, $matches);
    
    // Capture the template data from the template file's path.
    $result['group']['number'] = $matches['groupNo'] !== '' ? $matches['groupNo'] : preg_replace('/-[a-z-]+$/', '', $dir);
    $result['group']['name'] = $matches['groupName'] !== '' ? $matches['groupName'] : preg_replace('/^\d{1,2}-/', '', $dir);
    $result['subgroup']['number'] = $matches['subgroupNo'] !== '' ? $matches['subgroupNo'] : null;
    $result['subgroup']['name'] = $matches['subgroupName'] !== '' ? $matches['subgroupName'] : null;
    $result['pattern']['number'] = $matches['patternNo'] !== '' ? $matches['patternNo'] : null;
    $result['pattern']['name'] = $matches['patternName'];
    
    // Return the result.
    return $result;
    
  }

  // Extract the PLID of a template based on the template file's path.
  public static function plid( $path ) {
    
    // Parse the template file's path.
    $parts = self::parse($path);
    
    // Get the parts needed to build the template's PLID.
    $group = $parts['group']['name'];
    $pattern = $parts['pattern']['name'];
    
    // Return the template's PLID.
    return (isset($group) ? "$group-" : '').str_replace('~', '-', $pattern); 
    
  }
  
  // Extract the ID of a template based on the template file's path.
  public static function id( $path ) {
    
    // Return the template file's ID.
    return File::id($path);
    
  }
  
  // Extract the include path of a template based on the template file's path.
  public static function path( $path ) {
    
    // Parse the template file's path.
    $parts = self::parse($path);
    
    // Initialize the template's path.
    $path = '';
    
    // Build the template's path.
    $path .= (isset($parts['group']['number'])) ? $parts['group']['number'].'-' : '';
    $path .= (isset($parts['group']['name'])) ? $parts['group']['name'].'/' : '';
    $path .= (isset($parts['subgroup']['number'])) ? $parts['subgroup']['number'].'-' : '';
    $path .= (isset($parts['subgroup']['name'])) ? $parts['subgroup']['name'].'/' : '';
    $path .= (isset($parts['pattern']['name'])) ? $parts['pattern']['name'].'/' : '';
    
    // Return the template's path.
    return $path; 
    
  }
  
  // Extract the name of a template based on the template file's path.
  public static function name( $path ) {
    
    // Return the template's name.
    return self::parse($path)['pattern']['name'];
    
  }
  
}

?>