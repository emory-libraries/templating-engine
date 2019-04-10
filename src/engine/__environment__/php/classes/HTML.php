<?php

/**
 * HTML
 *
 * Makes creating HTML elements easier by following a jQuery approach.
 */
class HTML {
  
  private $tag;
  private $attrs = [];
  private $append = [];
  private $prepend = [];
  private $void = [
    'br',
    'hr',
    'input',
    'meta',
    'link',
    'img'
  ];
  
  /**
   * Constructs an HTML element.
   */
  function __construct( $tag, $attrs = [] ) {
    
    // Capture the tag.
    $this->tag = $tag;
    
    // Make sure text is set.
    if( !isset($attrs['text']) ) $attrs['text'] = '';
    
    // Capture the attributes.
    $this->attrs = $attrs;
    
  }
  
  /**
   * Add an attribute to the element or multiple attributes if given an array.
   */
  public function attr( $attr, $value = null ) {
    
    if( is_array($attr) ) $this->attrs = array_merge($this->attrs, $attr);
    
    else $this->attrs = array_merge($this->attrs, [$attr => $value]);
    
  }
  
  /**
   * Creates and outputs the element's HTML markup.
   */
  public function html() {
    
    // Determine if line breaks should be added before the text and closing tag.
    $break = (count($this->prepend) > 0 or count($this->append) > 0 or $this->attrs['text'] !== '');
    
    // Start the opening tag.
    $output = "<{$this->tag}";
    
    // Get attributes.
    $attrs = $this->attrs;
    
    // Extract text and styles.
    $text = $attrs['text'];
    $style = array_get($attrs, 'style', []);
    
    // Remove text and style from attributes.
    unset($attrs['text']);
    unset($attrs['style']);
    
    // Add the attributes.
    $output .= " ".array_to_attr($attrs);
    
    // Add styles.
    if( is_string($style) and $style !== '' ) $output .= " ".$style;
    if( is_array($style) and count($style) > 0 ) $output .= " ".array_to_css($style);
    
    // End the opening tag.
    $output .= ">";
    
    // Output void elements.
    if( in_array($this->tag, $this->void) ) return $output;
    
    // Prepend elements.
    foreach( $this->prepend as $element ) { $output .= "\n".$element->html(); }
    
    // Add text.
    $output .= ($break ? "\n" : "").$text;
    
    // Append elements.
    foreach( $this->append as $element ) { $output .= "\n".$element->html(); }
    
    // Add the closing tag.
    $output .= ($break ? "\n" : "")."</{$this->tag}>";
    
    // Output non-void elements.
    return $output;
    
  }
  
  /** 
   * Append one or more elements to the current one.
   */
  public function append( ...$elements ) {
    
    foreach( $elements as $element ) { 
      
      if( get_class($element) == __CLASS__ ) $this->append[] = $element;

    }
    
    return $this;
    
  }
  
  /** 
   * Prepend one or more elements to the current one.
   */
  public function prepend( ...$elements ) {
    
    foreach( $elements as $element ) { 
      
      if( get_class($element) == __CLASS__ ) $this->prepend[] = $element;

    }
    
    return $this;
    
  }
  
  /**
   * Append this element to another one.
   */
  public function appendTo( $element ) {
    
    if( get_class($element) != __CLASS__ ) return false;
    
    $element->append( $this );
    
  }
  
  /**
   * Prepend this element to another one.
   */
  public function prependTo( $element ) {
    
    if( get_class($element) != __CLASS__ ) return false;
    
    $element->prepend( $this );
    
  }
  
  /** 
   * Clone an HTML element while preserving the original element.
   */
  public function _clone() {
    
    return new HTML($this->tag, $this->attrs);
    
  }
  
}

?>