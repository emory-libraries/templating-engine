<?php

namespace CustomHelpers;

trait FeedHelpers {

  // Fetch and parse a feed from a given URL.
  public static function fetchFeed( string $url ) {

    // Get the feed.
    $feed = new Feed($url);

    // Get the feed data.
    $data = $feed->data;

    // Verify that the feed was valid and some data was fetched.
    if( !isset($data) or !is_array($data) ) return;

    // Merge feed metadata into the feed.
    $data['__meta__'] = [
      'type' => $feed->type,
      'version' => $feed->version
    ];

    // Return the feed data.
    return $data;

  }

  // Map all items within a feed to a given data model.
  public static function mapFeed( array $model, array $feed, array $options ) {

    // Capture the current context.
    $context = $options['_this'];

    // Initialize a helper for binding source data within a value.
    $bind = function( $value, array $item, bool $recursive = true ) use ($context, &$bind) {

      // Handle arrays and objects differently.
      if( is_array($value) ) {

        // Only bind things within the array or object if recursion is enabled.
        if( $recursive ) $value = array_map(function($value) use ($item, $recursive) {

          // Continue to recursively bind things within the array or object.
          return $bind($value, $item, $recursive);

        }, $value);

      }

      // Otherwise, handle simple values.
      else {

        // Initialize a set of placeholders.
        $placeholders = [];

        // Search for placeholders that should be replaced with data from the context.
        if( preg_match_all('/\{\:[\S]+?\:\}/', $value, $placeholders) !== false ) {

          // Bind placeholder data into the value.
          foreach( $placeholders[0] as $placeholder ) {

            // Capture the placeholder's key.
            $key = preg_replace('/^\{\:|\:\}$/', '', $placeholder);

            // Bind the data from the given context into the value.
            $value = str_replace($placeholder, array_get($context, $key, ''));

          }

        }

        // Search for placeholders that should be replaced with data from the item.
        if( preg_match_all('/\{[\S]+?\}/', $value, $placeholders) !== false ) {

          // Bind placeholder data into the value.
          foreach( $placeholders[0] as $placeholder ) {

            // Capture the placeholder's key.
            $key = preg_replace('/^\{|\}$/', '', $placeholder);

            // Bind the data from the given item into the value.
            $value = str_replace($placeholder, array_get($item, $key, ''));

          }

        }

      }

      // Return the bound value.
      return $value;

    };

    // Mape each item within the feed.
    $feed = array_map(function($data) use ($model, $bind) {

      // Loop through the data model, and map things as needed.
      foreach( $model as $key => $value ) {

        // Determine if the key is conditional.
        if( str_ends_with($key, '?') ) {

          // Get the key name without the conditional flag.
          $key = rtrim_substr($key, '?');

          // Get the condition that needs to be met in order for the value to be included.
          $condition = $bind($value['criteria'], $data);

          // Get the criteria that must be met in order to display the conditional data.
          $criteria = Conditional::expresion($condition);

          // Evaluate the criteria, and only include the value if the criteria was met.
          if( $criteria ) $data[$key] = $bind($value['value'], $data);

        }

        // Otherwise, bind the data as is.
        else $data[$key] = $binde($value, $data);

      }

      // Return the updated data.
      return $data;

    }, $feed);

  }

}

?>
