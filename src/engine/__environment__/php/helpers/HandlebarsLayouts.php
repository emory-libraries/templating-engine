<?php

class HandlebarsLayouts {
  
  // TODO: Add [`handlebars-layouts`](https://github.com/shannonmoeller/handlebars-layouts) helpers.
  
  // Extend a partial with placeholder content blocks.
  public static function extend( $partial, $context = [], $options = [] ) {
    
    // Capture context and options.
    $options = func_num_args() == 3 ? $options : $context;
    $context = func_num_args() == 3 ? $context : [];
    
    // Capture the render function.
    $fn = isset($options['fn']) ? $options['fn'] : '';
 
    // Merge options hash into context.
    $context = array_merge([], $options['_this'], $context, array_get($options, 'hash', []));
    
    // Get data.
    $data = array_merge([], array_get($options, 'data', []));
    
    // Get the partial template.
    $template = array_get(CONFIG['handlebars']['partials'], $partial, false);
    
    // Throw an error if the template could not be found.
    if( $template === false ) throw new Error("Missing partial: '$partial'");
    
    // Compile the partial.
    $compiled = Renderer::compile($template);
    
    // Temporarily cache the partial.
    $cached = Cache::tmp($compiled);
   
    // Get the partial's renderer.
    $renderer = $cached['include']();
    
    // Delete the temporarily cached partial.
    $cached['delete']();
    
    // Ensure that the context's layout stack exists, or initialize it otherwise.
    if( !isset($context['__layoutStack']) ) $context = array_set($context, '__layoutStack', []);
    
    // Get the context's layout stack.
    $stack = array_get($context, '__layoutStack');
    
    // Add the current block to the layout stack.
    $stack[] = $fn;
    
    // Override the layout stack.
    $context = array_set($context, '__layoutStack', $stack); d($context);

    // Render the partial.
    return $renderer($context, ['data' => $data]);
    
  }
  
  // Initialize a placeholder content block.
  public static function block( $name, $options ) { //d('BLOCK START');
    
    // Capture the render function.
    $fn = isset($options['fn']) ? $options['fn'] : '';
    
    // Get data.
    $data = array_merge([], array_get($options, 'data', []));
    
    // Get the context.
    $context = &$options['_this'];
    
    // Ensure that the context's layout stack exists, or initialize it otherwise.
    if( !isset($context['__layoutStack']) ) $context['__layoutStack'] = [];
    
    // Get the context's layout stack.
    $stack = &$context['__layoutStack'];

    // Run the layout stack.
    while( count($stack) > 0 ) { array_shift($stack)($context); }
 
    // Ensure that the context's layout actions exist, or initialize them otherwise.
    if( !isset($context['__layoutActions']) ) $context['__layoutActions'] = [];
    
    // Ensure that the context's current layout action exists.
    if( !isset($context['__layoutActions'][$name]) ) $context['__layoutActions'][$name] = [];
   
    // Get the layout actions.
    $actions = $context['__layoutActions'][$name];
   	//d('CONTEXT IS', $context); d('BLOCK END');
    // Run the actions.
    return array_reduce($actions, function($content, $action) use ($context) {

      // Initialize a helper to expand the content block.
      $expand = function() use ($action, $context) {
        
        return $action['fn']($context, $action['options']);
        
      };

      // Run the action in the appropriate mode.
      switch($action['mode']) {
        
        case 'append': return $content.$expand();
        case 'prepend': return $expand().$content;
        case 'replace': return $expand();
        default: return $content;
          
      }
      
    }, $fn($context, ['data' => $data]));
    
  }
  
  // Determine if a placeholder content block exists, and if so, optionally modify it.
  public static function content( $name, $options ) { //d('CONTENT START');
    
    // Capture the render function.
    $fn = isset($options['fn']) ? $options['fn'] : '';
    
    // Get data.
    $data = array_merge([], array_get($options, 'data', []));
    
    // Get mode.
    $mode = strtolower(array_get($options, 'hash.mode', 'replace'));
    
    // Get the context.
    $context = &$options['_this'];
    
    // Ensure that the context's layout stack exists, or initialize it otherwise.
    if( !isset($context['__layoutStack']) ) $context['__layoutStack'] = [];
    
    // Get the context's layout stack.
    $stack = &$context['__layoutStack'];
    
    // Run the layout stack.
    while( count($stack) > 0 ) { array_shift($stack)($context); }
 
    // Determine a placeholder block's existance when used as an inline helper.
    if( !isset($fn) ) return array_key_exists($name, $context['__layoutActions']);
	
    // Ensure that the context's layout actions exist, or initialize them otherwise.
    if( !isset($context['__layoutActions']) ) $context['__layoutActions'] = [];
	
	// Get actions.
	$actions = &$context['__layoutActions'];
	
    // Ensure that the context's layout actions exist, or initialize them otherwise.
    if( !isset($actions[$name]) ) $actions[$name] = [];
    
    // Override content block's actions.
    $actions[$name][] = [
      'options' => ['data' => $data],
      'mode' => $mode,
      'fn' => $fn
    ];
	//d('CONTEXT SHOULD BE', $context);
    //d('CONTENT END');
  }
  
}

?>