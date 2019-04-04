<?php

namespace HandlebarsLogging;

trait LogHelpers {
  
  // Helper for logging an simple message to the terminal.
  public static function log( ...$messages ) {
    
    // Extract options.
    $options = _::last($messages);
    $messages = _::initial($messages);
    
    // Format messages.
    $messages = implode(', ', array_map(function ($message) {
      
      return json_encode($message);
      
    }, $messages));
    
    // Log messages.
    return "<script>console.log({$messages})</script>";
    
  }
  
  // Helper for logging an "ok" message to the console preceeded by a checkmark.
  public static function ok( ...$messages ) {
    
    // Extract options.
    $options = _::last($messages);
    $messages = _::initial($messages);
    
    // Define styles.
    $styles = implode('; ', array_css([
      'color' => 'green'
    ]));
    
    // Format messages.
    $messages = implode("\n", array_map(function ($message) use ($styles) {
      
      return 'console.log("%c ✓ %s", "'.$styles.'", '.json_encode($message).')';
      
    }, $messages));
    
    // Log messages.
    return "<script>{$messages}</script>";
    
  }
  
  // Helper for logging a "success" message to the console.
  public static function success( ...$messages ) {
    
    // Extract options.
    $options = _::last($messages);
    $messages = _::initial($messages);
    
    // Define styles.
    $styles = implode('; ', array_css([
      'color' => 'green'
    ]));
    
    // Format messages.
    $messages = implode("\n", array_map(function ($message) use ($styles) {
      
      return 'console.log("%c %s", "'.$styles.'", '.json_encode($message).')';
      
    }, $messages));
    
    // Log messages.
    return "<script>{$messages}</script>";
    
  }
  
  // Helper for logging an "info" message to the console.
  public static function info( ...$messages ) {
    
    // Extract options.
    $options = _::last($messages);
    $messages = _::initial($messages);
    
    // Format messages.
    $messages = implode(", ", array_map(function ($message) {
      
      return json_encode($message);
      
    }, $messages));
    
    // Log messages.
    return "<script>console.info({$messages})</script>";
    
  }
  
  // Helper for logging a "warning" message to the console.
  public static function warning( ...$messages ) {
    
    // Extract options.
    $options = _::last($messages);
    $messages = _::initial($messages);
    
    // Format messages.
    $messages = implode(", ", array_map(function ($message) {
      
      return json_encode($message);
      
    }, $messages));
    
    // Log messages.
    return "<script>console.warn({$messages})</script>";
    
  }
  
  // Helper for logging a "warn" message to the console. [alias]
  public static function warn( ...$messages ) { 
    
    // Extract options.
    $options = _::last($messages);
    $messages = _::initial($messages);
    
    // Format messages.
    $messages = implode(", ", array_map(function ($message) {
      
      return json_encode($message);
      
    }, $messages));
    
    // Log messages.
    return "<script>console.warn({$messages})</script>";
  
  }
  
  // Helper for logging an "error" message to the console.
  public static function error( ...$messages ) {
    
    // Extract options.
    $options = _::last($messages);
    $messages = _::initial($messages);
    
    // Format messages.
    $messages = implode(", ", array_map(function ($message) {
      
      return json_encode($message);
      
    }, $messages));
    
    // Log messages.
    return "<script>console.error({$messages})</script>";
    
  }
  
  // Helper for logging a "danger" message to the console. [alias]
  public static function danger( ...$messages ) {
    
    // Extract options.
    $options = _::last($messages);
    $messages = _::initial($messages);
    
    // Format messages.
    $messages = implode(", ", array_map(function ($message) {
      
      return json_encode($message);
      
    }, $messages));
    
    // Log messages.
    return "<script>console.error({$messages})</script>";
  
  }
  
  // Helper for logging a "bold" message to the console.
  public static function bold( ...$messages ) {
    
    // Extract options.
    $options = _::last($messages);
    $messages = _::initial($messages);
    
    // Define styles.
    $styles = implode('; ', array_css([
      'font-weight' => 'bold'
    ]));
    
    // Format messages.
    $messages = implode("\n", array_map(function ($message) use ($styles) {
      
      return 'console.log("%c %s", "'.$styles.'", '.json_encode($message).')';
      
    }, $messages));
    
    // Log messages.
    return "<script>{$messages}</script>";
    
  }
  
}

?>