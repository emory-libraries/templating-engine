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
  
  // Constructs the data.
  function __construct( $path ) {
    
    // Save the template file.
    $this->file = $path;
    
    // Read the template file at the given path.
    $this->template = file_get_contents($path);
    
    // Get the template file's ID.
    $this->id = File::id($path);
    
    // Define regex parts for extracting pattern data from the file path.
    $regex = [
      'group' => '(?:(?:(?P<groupNo>\d{1,2})-)?(?P<groupName>[a-z-]+?(?=-)))?',
      'subgroup' => '(?:\-(?:(?P<subgroupNo>\d{1,2})-)?(?P<subgroupName>[a-z-]+?(?=-)))?',
      'pattern' => '(?:-?(?:(?P<patternNo>\d{1,2})\-)?(?P<patternName>[a-z0-9\~\-\_]+))'
    ];
    
    // Locate the template file's PLID and path.
    preg_match('/^'.implode('', array_values($regex)).'$/i', $this->id, $matches);
    
    // Get template data from the template file name.
    $groupNo = array_get($matches, 'groupNo') ?: preg_replace('/-[a-z-]+$/', '', basename(dirname($path)));
    $groupName = array_get($matches, 'groupName') ?: preg_replace('/^\d{1,2}-/', '', basename(dirname($path)));
    $subgroupNo = array_get($matches, 'subgroupNo');
    $subgroupName = array_get($matches, 'subgroupName');
    $patternNo = array_get($matches, 'patternNo');
    $patternName = array_get($matches, 'patternName');
    
    // Save relevant template data.
    $this->name = $patternName;
    $this->plid = ($groupName !== "" ? "$groupName-" : "").$patternName;
    $this->path = ($groupNo !== "" ? "$groupNo-" : "").
                  ($groupName !== "" ? "$groupName/" : "").
                  ($subgroupNo !== "" ? "$subgroupNo-" : "").
                  ($subgroupName !== "" ? "$subgroupName/" : "").
                  ($patternNo !== "" ? "$patternNo-" : "").
                  $patternName;
    
  }
  
}

?>