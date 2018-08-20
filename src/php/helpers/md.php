<?php

return function( $template, $options = [] ) {
  
  // Get context.
  $context = is_array($template) ? $template['_this'] : $options['_this'];
 
  // Initialize the markdown engine.
  $markdown = new Md([
    'useSafeMode'         => array_get($context, '__config__.useSafeMode'),
    'headerLevelStart'    => array_get($context, '__config__.headerLevelStart'),
    'enabledHeaderIds'    => array_get($context, '__config__.enableHeaderIds'),
    'overwriteHeaderIds'  => array_get($context, '__config__.overwriteHeaderIds'),
    'disableImages'       => array_get($context, '__config__.disableImages')
  ]);
  
  // Initialize the mustache engine.
  $mustache = new Mustache_Engine();
  
  // Render any mustache.
  $rendered = $mustache->render((is_array($template) ? $template['fn']() : $template), $context);
  
  // Render the markdown.
  return $markdown->text($rendered);
  
};

?>