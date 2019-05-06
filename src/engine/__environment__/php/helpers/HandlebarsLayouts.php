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
    $context = array_set($context, '__layoutStack', $stack); 

    // Render the partial.
    return $renderer($context, ['data' => $data]);
    
  }
  
  // Initialize a placeholder content block.
  public static function block( $name, $options ) {
    
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
    
    // Capture rendered block contexts.
    $result = [];

    // Run the layout stack.
    while( count($stack) > 0 ) { $result[] = json_decode(array_shift($stack)($context), true); }
    
    // Ensure that the context's layout actions exist, or initialize them otherwise.
    if( !isset($context['__layoutActions']) ) $context['__layoutActions'] = [];
    
    // Merge rendered block contexts back into the current context.
    $context['__layoutActions'] = array_merge($context['__layoutActions'], ...array_map(function($content) {
      
      // Get the rendered content's layout actions.
      return array_get($content, '__layoutActions', []);
      
    }, $result));
    
    // Ensure that the context's current layout action exists.
    if( !isset($context['__layoutActions'][$name]) ) $context['__layoutActions'][$name] = [];
    
    // Get the layout actions.
    $actions = $context['__layoutActions'][$name];
  
    // Initialize a helper to expand content blocks.
    $expand = function($action) use ($context, $parser) {

      // Run the action.
      return $action['fn']($context, $action['options']);

    };
    
    // Run the actions.
    return array_reduce($actions, function($content, $action) use ($context, $expand) {

      // Run the action in the appropriate mode.
      switch($action['mode']) {
        
        case 'append': return $content.$expand();
        case 'prepend': return $expand().$content;
        case 'replace': return $expand($action);
        default: return $content;
          
      }
      
    }, $fn($context, ['data' => $data]));
    
  }
  
  // Determine if a placeholder content block exists, and if so, optionally modify it.
  public static function content( $name, $options ) {
    
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
      'fn' => $fn // FIXME: Cannot serialize Closure!!
    ];

	d('CONTEXT SHOULD BE', $context);
    return json_encode($context);
    
  }
  
}

?>