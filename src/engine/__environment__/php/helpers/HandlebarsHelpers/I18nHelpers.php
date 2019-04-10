<?php

namespace HandlebarsHelpers;

use _;

trait I18nHelpers {
  
  // Internationalization (i18n) helper. See [button-i18n](https://github.com/assemble/buttons) for a working example.
  public static function i18n( string $property, $locales = [], $options = [] ) {
    
    // Get arguments.
    $arguments = func_get_args();
    $options = _::last($arguments);
    $locales = func_num_args() == 3 ? $locales : array_get($options['_this'], 'locales', []);
    $language = array_get($options, 'language', array_get($options, 'lang')) ?: array_get($options, 'hash.language', array_get($options, 'hash.lang', 'en'));

    // Localize options.
    $context = array_merge([], $locales, array_get($options['_this'], 'locales', []), $options['_this']); 
    
    // Get the localized context.
    $cache = array_get($context, $language, false);
    
    // Throw an error if the localized context was not found.
    if( $cache === false ) throw new Error("`i18n` helper cannot find language '{$language}'");
    
    // Get the localized property.
    $localized = array_get($cache, $property, false);
    
     // Throw an error if the localized property was not found.
    if( $localized === false ) throw new Error("`i18n` helper cannot find property '{$property}'");
    
    // Return the localized property.
    return $localized;
    
  }
  
}

?>