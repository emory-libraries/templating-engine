<?php

use Moment\Moment;

/**
 * Mutator
 *
 * Mutates a given data model to reflect
 * a more desirable data model based on a
 * set of template-specific mutations.
 */
class Mutator {

  // Set the order of mutations.
  protected static $order = [
    'repeatable',
    'checkbox',
    'radio',
    'timestamp',
    'text',
    'html',
    'alias',
    'rename',
    'replace',
    'remove',
    'add',
    'merge',
    'evaluate'
  ];

  // Sets a constant to a known undefined value.
  public static $undefined = 'MUTATOR_UNDEFINED';

  // Mutate some data based on a set of template-specific data mutations.
  public static function mutate( array $data, $template, $globals = false ) {

    // Look for any template-specific mutations.
    $mutations = array_get(CONFIG['config']['mutations'], $template, []);

    // Only use globals if indicated.
    if( $globals === true ) {

      // Convert the mutations array to a collection.
      $mutations = [$mutations];

      // Add global-level mutations to the template-specific mutations.
      if( isset(CONFIG['config']['mutations']['globals']) ) {

        // Add each set of global mutations in order so that they can be applied one after the other.
        foreach( CONFIG['config']['mutations']['globals'] as $id => $global ) {

          // Add the mutations.
          $mutations[] = $global;

        }

      }

    }

    // Return the unmutated data if no mutations exist.
    if( empty($mutations) ) return $data;

    // Apply the mutations to the data.
    $data = static::apply($data, $mutations);

    // Return the mutated data.
    return $data;

  }

  // Apply all mutations to the given data.
  protected static function apply( array $data, array $mutations ) {

    // For ordered mutations, apply them recursively.
    if( !is_associative_array($mutations) ) {

      // Mutate the data recursively.
      foreach( $mutations as $mutation ) {

        // Recursively apply the mutations to the data.
        $data = static::apply($data, $mutation);

      }

    }

    // Otherwise, use the mutations to mutate the data.
    else {

      // Loop through the order of mutations, and apply each one.
      foreach( static::$order as $method ) {

        // Send  the data through the mutation method.
        $data = static::{$method}($data, array_get($mutations, $method, []));

      }

    }

    // Return the mutated data.
    return $data;

  }

  // Make values within a data set repeatable by forcing them to be non-associative arrays.
  public static function repeatable( array $data, $repeatables ) {

    // Convert strings to an array.
    $repeatables = is_string($repeatables) ? [$repeatables] : $repeatables;

    // Make the keys repeatable one by one.
    foreach( $repeatables as $repeatable ) {

      // Mutate items within an array.
      if( strpos($repeatable, '@') !== false ) {

        // Get the keys.
        $keys = array_map(function($key) {

          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');

        }, explode('@', $repeatable));

        // Look for the array of objects.
        if( array_get($data, $keys[0], self::$undefined) !== self::$undefined ) {

          // Get the array of objects.
          $array = array_get($data, $keys[0]);

          // Mutate each object within the array.
          foreach( $array as $index => $object ) {

            // Mutate the object's checkbox keys.
            $array[$index] = self::repeatable($object, implode('.@.', array_slice($keys, 1)));

          }

          // Save the array.
          $data = array_set($data, $keys[0], $array);

        }

      }

      // Otherwise, mutate the items within an object.
      else {

        // Look for the key within the data set.
        if( array_get($data, $repeatable, self::$undefined) !== self::$undefined ) {

          // Get the existing value.
          $value = array_get($data, $repeatable);

          // Force the key's value to be a non-associative array.
          if( is_array($value) and is_associative_array($value) ) {

            // Mutate the value.
            $data = array_set($data, $repeatable, [$value]);

          }

        }

      }

    }

    // Return the mutated data.
    return $data;

  }

  // Capture checkbox values within a data set.
  public static function checkbox( array $data, $checkboxes ) {

    // Convert strings to an array.
    $checkboxes = is_string($checkboxes) ? [$checkboxes] : $checkboxes;

    // Mutate the checkboxes one by one.
    foreach( $checkboxes as $checkbox ) {

      // Mutate items within an array.
      if( strpos($checkbox, '@') !== false ) {

        // Get the keys.
        $keys = array_map(function($key) {

          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');

        }, explode('@', $checkbox));

        // Look for the array of objects.
        if( array_get($data, $keys[0], self::$undefined) !== self::$undefined ) {

          // Get the array of objects.
          $array = array_get($data, $keys[0]);

          // Mutate each object within the array.
          foreach( $array as $index => $object ) {

            // Mutate the object's checkbox keys.
            $array[$index] = self::checkbox($object, implode('.@.', array_slice($keys, 1)));

          }

          // Save the array.
          $data = array_set($data, $keys[0], $array);

        }

      }

      // Otherwise, mutate items within an object.
      else {

        // Look for the key within the data set.
        if( array_get($data, $checkbox, self::$undefined) !== self::$undefined ) {

          // Get the checkbox item.
          $item = array_get($data, $checkbox);

          // Determine if the checkbox has a value, and if so, extract it.
          if( array_get($item, 'value', false) ) {

            // Get the checkbox value(s).
            $value = array_get($item, 'value');

            // Convert boolean-like values to a boolean.
            if( !is_array($value) and Cast::isBool($value) ) $value = Cast::toBool($value);

            // Save the value(s) of the checkbox.
            $data = array_set($data, $checkbox, $value);

          }

          // Otherwise, unchecked checkboxes should have a false value by default.
          else $data = array_set($data, $checkbox, false);

        }

      }

    }

    // Return the mutated data.
    return $data;

  }

  // Capture radio button values within a data set.
  public static function radio( array $data, $radios ) {

    // Convert strings to an array.
    $radios = is_string($radios) ? [$radios] : $radios;

    // Mutate the radio buttons one by one.
    foreach( $radios as $radio ) {

      // Mutate items within an array.
      if( strpos($radio, '@') !== false ) {

        // Get the keys.
        $keys = array_map(function($key) {

          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');

        }, explode('@', $radio));

        // Look for the array of objects.
        if( array_get($data, $keys[0], self::$undefined) !== self::$undefined ) {

          // Get the array of objects.
          $array = array_get($data, $keys[0]);

          // Mutate each object within the array.
          foreach( $array as $index => $object ) {

            // Mutate the object's radio keys.
            $array[$index] = self::radio($object, implode('.@.', array_slice($keys, 1)));

          }

          // Save the array.
          $data = array_set($data, $keys[0], $array);

        }

      }

      // Otherwise, mutate items within an object.
      else {

        // Look for the key within the data set.
        if( array_get($data, $radio, self::$undefined) !== self::$undefined ) {

          // Get the radio item.
          $item = array_get($data, $radio);

          // Convert empty radio buttons to a null value by default.
          if( !isset($item) or empty($item) ) $data = array_set($data, $radio, null);

        }

      }

    }

    // Return the mutated data.
    return $data;

  }

  // Convert timestamps to dates within a data set.
  public static function timestamp( array $data, $timestamps ) {

    // Convert strings to an array.
    $timestamps = is_string($timestamps) ? [$timestamps] : $timestamps;

    // Mutate the timestamps one by one.
    foreach( $timestamps as $timestamp ) {

      // Mutate items within an array.
      if( strpos($timestamp, '@') !== false ) {

        // Get the keys.
        $keys = array_map(function($key) {

          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');

        }, explode('@', $timestamp));

        // Look for the array of objects.
        if( array_get($data, $keys[0], self::$undefined) !== self::$undefined ) {

          // Get the array of objects.
          $array = array_get($data, $keys[0]);

          // Mutate each object within the array.
          foreach( $array as $index => $object ) {

            // Mutate the object's radio keys.
            $array[$index] = self::timestamp($object, implode('.@.', array_slice($keys, 1)));

          }

          // Save the array.
          $data = array_set($data, $keys[0], $array);

        }

      }

      // Otherwise, mutate items within an object.
      else {

        // Look for the key within the data set.
        if( array_get($data, $timestamp, self::$undefined) !== self::$undefined ) {

          // Get the timestamp value.
          $value = array_get($data, $timestamp);

          // Verify that the timestamp is valid.
          if( isset($value) and is_int($value) ) {

            // Since the CMS outputs UNIX timestamps, remove milliseconds from the timestamp.
            $value = (int) floor($value / 1000);

            // Convert the timestamp to a datetime.
            $datetime = (new DateTime())->setTimestamp($value);

            // Convert the datetime to a moment.
            $moment = Moment::fromDateTime($datetime);

            // Save the moment.
            $data = array_set($data, $timestamp, $moment, true);

          }

        }

      }

    }

    // Return the mutated data.
    return $data;

  }

  // Mutate HTML fields within the data set. [alias]
  public static function html( array $data, $html ) {

    return forward_static_call('Mutator::text', $data, $html);

  }

  // Mutate text fields within the data set.
  public static function text( array $data, $texts ) {

    // Convert strings to an array.
    $texts = is_string($texts) ? [$texts] : $texts;

    // Mutate the text one by one.
    foreach( $texts as $text ) {

      // Mutate items within an array.
      if( strpos($text, '@') !== false ) {

        // Get the keys.
        $keys = array_map(function($key) {

          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');

        }, explode('@', $text));

        // Look for the array of objects.
        if( array_get($data, $keys[0], self::$undefined) !== self::$undefined ) {

          // Get the array of objects.
          $array = array_get($data, $keys[0]);

          // Mutate each object within the array.
          foreach( $array as $index => $object ) {

            // Mutate the object's text keys.
            $array[$index] = self::text($object, implode('.@.', array_slice($keys, 1)));

          }

          // Save the array.
          $data = array_set($data, $keys[0], $array);

        }

      }

      // Otherwise, mutate items within an object.
      else {

        // Look for the key within the data set.
        if( array_get($data, $text, self::$undefined) !== self::$undefined ) {

          // Get the text item.
          $item = array_get($data, $text);

          // Convert empty text to an empty string.
          if( !isset($item) or empty($item) ) $data = array_set($data, $text, "");

        }

      }

    }

    // Return the mutated data.
    return $data;

  }

  // Mutate the data by aliasing keys within the data set.
  public static function alias( array $data, array $aliases ) {

    // Alias keys as different keys.
    foreach( $aliases as $alias => $source ) {

      // Alias items within an array.
      if( strpos($source, '@') !== false ) {

        // Get the keys for the alias and source.
        $aliasKeys = array_map(function($key) {

          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');

        }, explode('@', $alias));
        $sourceKeys = array_map(function($key) {

          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');

        }, explode('@', $source));

        // For array aliases, only permit aliasing within the same array item.
        if( count($aliasKeys) != count($sourceKeys) or $aliasKeys[0] != $sourceKeys[0] ) continue;

        // Look for the array of objects.
        if( array_get($data, $sourceKeys[0], self::$undefined) !== self::$undefined ) {

          // Get the array of objects.
          $array = array_get($data, $sourceKeys[0]);

          // Mutate each object within the array.
          foreach( $array as $index => $object ) {

            // Mutate the object's text keys.
            $array[$index] = self::alias($object, [implode('.@.', array_slice($aliasKeys, 1)) => implode('.@.', array_slice($sourceKeys, 1))]);

          }

          // Save the array.
          $data = array_set($data, $sourceKeys[0], $array, true);

        }

      }

      // Otherwise, only try to alias keys that actually exists.
      else if( array_get($data, $source, self::$undefined) !== self::$undefined ) {

        // Save the alias, but disallow overwriting of keys that already exist.
        $data = array_set($data, $alias, array_get($data, $source));

      }

    }

    // Return the mutated data.
    return $data;

  }

  // Mutate the data by renaming keys within the data set.
  public static function rename( array $data, array $renames ) {

    // Rename keys as different keys.
    foreach( $renames as $new => $old ) {

      // Rename items within an array.
      if( strpos($old, '@') !== false ) {

        // Get the keys for new and old.
        $newKeys = array_map(function($key) {

          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');

        }, explode('@', $new));
        $oldKeys = array_map(function($key) {

          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');

        }, explode('@', $old));

        // For array renames, only permit renaming within the same array item.
        if( count($newKeys) != count($oldKeys) or $newKeys[0] != $oldKeys[0] ) continue;

        // Look for the array of objects.
        if( ($array = array_get($data, $oldKeys[0], self::$undefined)) !== self::$undefined ) {

          // Mutate each object within the array.
          foreach( $array as $index => $object ) {

            // Mutate the object's text keys.
            $array = array_set($array, $index, self::rename($object, [implode('.@.', array_slice($newKeys, 1)) => implode('.@.', array_slice($oldKeys, 1))]), true);

          }

          // Save the array.
          $data = array_set($data, $oldKeys[0], $array, true);

        }

      }

      // Otherwise, only try to rename keys that actually exists.
      else if( array_get($data, $old, self::$undefined) !== self::$undefined ) {

        // Save the renamed key.
        $data = array_set($data, $new, array_get($data, $old), true);

        // Remove the old key.
        $data = array_unset($data, $old);

      }

    }

    // Return the mutated data.
    return $data;

  }

  // Mutate the data by replacing values within the data set.
  public static function replace( array $data, array $replaces ) {

    // Replace values with different values.
    foreach( $replaces as $key => $value ) {

      // Replace values within an array.
      if( strpos($key, '@') !== false ) {

        // Get the keys.
        $keys = array_map(function($key) {

          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');

        }, explode('@', $key));

        // Look for the array of objects.
        if( array_get($data, $keys[0], self::$undefined) !== self::$undefined ) {

          // Get the array of objects.
          $array = array_get($data, $keys[0]);

          // Mutate each object within the array.
          foreach( $array as $index => $object ) {

            // Replace the array item's key with the new value.
            $array[$index] = array_set($array[$index], implode('.@.', array_slice($keys, 1)), $value, true);

          }

          // Save the array.
          $data = array_set($data, $keys[0], $array, true);

        }

      }

      // Otherwise, only try to replace keys that actually exists.
      else if( array_get($data, $key, self::$undefined) !== self::$undefined ) {

        // Replace the key's existing value with the new value.
        $data = array_set($data, $key, $value, true);

      }

    }

    // Return the mutated data.
    return $data;

  }

  // Mutate the data by removing keys within the data set.
  public static function remove( array $data, $removes ) {

    // Convert strings to arrays.
    $removes = is_string($removes) ? [$removes] : $removes;

    // Remove keys with the data set.
    foreach( $removes as $remove ) {

      // Remove values within an array.
      if( strpos($remove, '@') !== false ) {

        // Get the keys.
        $keys = array_map(function($key) {

          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');

        }, explode('@', $remove));

        // Look for the array of objects.
        if( array_get($data, $keys[0], false) !== false ) {

          // Get the array of objects.
          $array = array_get($data, $keys[0]);

          // Mutate each object within the array.
          foreach( $array as $index => $object ) {

            // Remove the array item's key with the new value.
            $array[$index] = array_unset($array[$index], implode('.@.', array_slice($keys, 1)), true);

          }

          // Save the array.
          $data = array_set($data, $keys[0], $array, true);

        }

      }

      // Otherwise, remove any keys that may exist.
      else {

        // Remove any keys that exist.
        $data = array_unset($data, $remove);

      }

    }

    // Return the mutated data.
    return $data;

  }

  // Mutate the data by adding keys to the data set.
  public static function add( array $data, array $adds ) {

    // Add keys to the data set.
    foreach( $adds as $key => $value ) {

      // Replace values within an array.
      if( strpos($key, '@') !== false ) {

        // Get the keys.
        $keys = array_map(function($key) {

          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');

        }, explode('@', $key));

        // Look for the array of objects.
        if( array_get($data, $keys[0], false) !== false ) {

          // Get the array of objects.
          $array = array_get($data, $keys[0]);

          // Mutate each object within the array.
          foreach( $array as $index => $object ) {

            // Replace the array item's key with the new value.
            $array[$index] = array_set($array[$index], implode('.@.', array_slice($keys, 1)), $value);

          }

          // Save the array.
          $data = array_set($data, $keys[0], $array, true);

        }

      }

      // Otherwise, add any keys that don't already exist.
      else {

        // Add any keys that don't already exist.
        $data = array_set($data, $key, $value);

      }

    }

    // Return the mutated data.
    return $data;

  }

  // Mutate the data by merging two or more arrays together.
  public static function merge( array $data, array $merges, $original = null ) {

    // Merge the keys one by one.
    foreach( $merges as $source => $targets ) {

      // Make the targets an array.
      $targets = is_array($targets) ? $targets : [$targets];

      // Mutate items within an array.
      if( strpos($source, '@') !== false ) {

        // Get the keys.
        $keys = array_map(function($key) {

          // Strip trailing and leading dots from the keys.
          return trim($key, '. ');

        }, explode('@', $source));

        // Look for the array of objects.
        if( array_get($data, $keys[0], self::$undefined) !== self::$undefined ) {

          // Get the array of objects.
          $array = array_get($data, $keys[0]);

          // Also, adjust targets to only use the given array of objects.
          $targets = array_map(function($target) use ($keys) {

            // Require that only the same object be used when looking within an object array.
            return str_replace($keys[0].'.@.', '', $target);


          }, $targets);

          // Mutate each object within the array.
          foreach( $array as $index => $object ) {

            // Get the name of the key to be merged.
            $key = implode('.@.', array_slice($keys, 1));

            // Mutate the object's checkbox keys.
            $array[$index] = self::merge($object, [$key => $targets], $original ?? $data);

          }

          // Save the array.
          $data = array_set($data, $keys[0], $array);

        }

      }

      // Otherwise, mutate the items within an object.
      else {

        // Look for the key within the data set.
        if( array_get($data, $source, self::$undefined) !== self::$undefined ) {

          // Get the existing value.
          $value = array_get($data, $source);

          // Ignore non-mergeable source values.
          if( !is_array($value) ) return $data;

          // Get all target values to be merged, and filter out any non-arrays.
          $values = array_values(array_filter(array_map(function($target) use ($data, $original) {

            // Locate the target value.
            return array_get($data, $target, array_get($original ?? [], $target));

          }, $targets), function($value) {

            // Filter out the value if it's not an array.
            return is_array($value);

          }));

          // Merge the target values into the source.
          $data = array_set($data, $source, array_merge([], $value, ...$values));

        }

      }

    }

    // Return the mutated data.
    return $data;

  }

  // Mutate the data by evaluating some condition.
  public static function evaluate( array $data, array $evaluates, $original = null ) {

    // Evaluate values with the given conditions.
    foreach( $evaluates as $eval ) {

      // Ignore invalid expressions.
      if( !array_key_exists('condition', $eval) or !array_key_exists('target', $eval) or !array_key_exists('value', $eval) ) continue;

      // Get the condition, target, and value.
      $target = $eval['target'];
      $condition = $eval['condition'];
      $value = $eval['value'];
      $evaluate = array_get($eval, 'eval', false);

      // If multiple conditions were given, then handle each one individually.
      if( is_array($condition) ) {

        // Expand the conditions into their own evaluate expressions.
        $expressions = [];

        // Apply each condition individually.
        foreach( $condition as $index => $expressionCondition ) {

          // Get the condition's respective value.
          $expressionValue = is_array($value) ? $value[$index] : $value;

          // Save the given expression.
          $expressions[] = [
            'target' => $target,
            'condition' => $expressionCondition,
            'value' => $expressionValue
          ];

        }

        // Then, mutate the expanded set of evaluate expressions.
        $data = static::evaluate($data, $expressions, $original);

      }

      // Otherwise, handle single conditions.
      else {

        // Evaluate values within an array.
        if( strpos($target, '@') !== false ) {

          // Set the number of levels that will move.
          $levels = 1;

          // Get the keys.
          $keys = array_map(function($key) {

            // Strip trailing and leading dots from the keys.
            return trim($key, '. ');

          }, explode('@', $target));

          // Look for the array of objects.
          if( array_get($data, $keys[0], self::$undefined) !== self::$undefined ) {

            // Get the array of objects.
            $array = array_get($data, $keys[0]);

            // Mutate each object within the array.
            foreach( $array as $index => $object ) {

              // Evaluate the array item's key with the new value.
              $array = array_set($array, $index, self::evaluate($array[$index], [[
                'condition' => str_replace($keys[0].'.@.', $keys[0].".$index.", $condition, $levels),
                'target' => implode('.@.', array_slice($keys, 1)),
                'value' => is_string($value) ? str_replace($keys[0].'.@.', $keys[0].".$index.", $value, $levels) : $value,
                'eval' => $evaluate
              ]], $original ?? $data));

            }

            // Save the array.
            $data = array_set($data, $keys[0], $array);

          }

        }

        // Otherwise, evaluate the target key.
        else {

          // Get the target's source.
          $source = array_get($data, $target);

          // Initialize regexes for replacing values.
          $regexes = [
            [
              "pattern" => "/{{$target}}/",
              "replacement" => function($value, $match, $condition = false) use ($source, $evaluate) {

                // Get the replacement.
                $replacement = $source;

                // For conditions, always replace as a string.
                if( $condition or $evaluate ) {

                  // If the replacement is a string, then wrap it in quotes.
                  if( is_string($replacement) ) $replacement = "'$replacement'";

                  // Otherwise, if the replacement is a boolean value, replace it with its boolean keyword.
                  else if( is_bool($replacement) ) $replacement = $replacement ? 'true' : 'false';

                  // Otherwise, convert the replacement to a string, but keep it as is.
                  else $replacement = var_export($replacement, true);

                  // Merge the string replacement.
                  return str_replace($match[0], $replacement, $value);

                }

                // Otherwise, merge strings, or return the replacement as is for everythin gelse.
                return (is_string($replacement) ? str_replace($match[0], $replacement, $value) : $replacement);

              }
            ],
            [
              "pattern" => '/{__this__}/',
              "replacement" => function($value, $match, $condition = false) use ($source, $evaluate) {

                // Get the replacement.
                $replacement = $source;

                // For conditions, always replace as a string.
                if( $condition or $evaluate ) {

                  // If the replacement is a string, then wrap it in quotes.
                  if( is_string($replacement) ) $replacement = "'$replacement'";

                  // Otherwise, if the replacement is a boolean value, replace it with its boolean keyword.
                  else if( is_bool($replacement) ) $replacement = $replacement ? 'true' : 'false';

                  // Otherwise, convert the replacement to a string, but keep it as is.
                  else $replacement = var_export($replacement, true);

                  // Merge the string replacement.
                  return str_replace($match[0], $replacement, $value);

                }

                // Otherwise, merge strings, or return the replacement as is for everythin gelse.
                return (is_string($replacement) ? str_replace($match[0], $replacement, $value) : $replacement);

              }
            ],
            [
              "pattern" => '/&{(\S+)}/',
              "replacement" => function($value, $match, $condition = false) use ($data, $original, $target, $evaluate) {

                // If not inside an array, then try to use the  source data.
                if( !isset($original) ) $data = array_get($data, implode('.', array_head(explode('.', $target))));

                // Get the replacement.
                $replacement = array_get($data, $match[1]);

                // For conditions, always replace as a string.
                if( $condition or $evaluate ) {

                  // If the replacement is a string, then wrap it in quotes.
                  if( is_string($replacement) ) $replacement = "'$replacement'";

                  // Otherwise, if the replacement is a boolean value, replace it with its boolean keyword.
                  else if( is_bool($replacement) ) $replacement = $replacement ? 'true' : 'false';

                  // Otherwise, convert the replacement to a string, but keep it as is.
                  else $replacement = var_export($replacement, true);

                  // Merge the string replacement.
                  return str_replace($match[0], $replacement, $value);

                }

                // Otherwise, merge strings, or return the replacement as is for everythin gelse.
                return (is_string($replacement) ? str_replace($match[0], $replacement, $value) : $replacement);

              }
            ],
            [
              "pattern" => '/{(\S+)}/',
              "replacement" => function($value, $match, $condition = false) use ($data, $original, $evaluate) {

                // Get the replacement.
                $replacement = array_get($original ?? $data, $match[1]);

                // For conditions, always replace as a string.
                if( $condition or $evaluate ) {

                  // If the replacement is a string, then wrap it in quotes.
                  if( is_string($replacement) ) $replacement = "'$replacement'";

                  // Otherwise, if the replacement is a boolean value, replace it with its boolean keyword.
                  else if( is_bool($replacement) ) $replacement = $replacement ? 'true' : 'false';

                  // Otherwise, convert the replacement to a string, but keep it as is.
                  else $replacement = var_export($replacement, true);

                  // Merge the string replacement.
                  return str_replace($match[0], $replacement, $value);

                }

                // Otherwise, merge strings, or return the replacement as is for everythin gelse.
                return (is_string($replacement) ? str_replace($match[0], $replacement, $value) : $replacement);
              }
            ]
          ];

          // Loop through the regex replacements, and inject values into the condition and/or value.
          foreach( $regexes as $regex ) {

            // Inject replacement values into the condition.
            if( preg_match_all($regex['pattern'], $condition, $matches, PREG_SET_ORDER) ) {

              // Loop through matches and replace each.
              foreach( $matches as $match ) { $condition = $regex['replacement']($condition, $match, true); }

            }

            // Also, replace within the value if the value is a string.
            if( is_string($value) ) {

              // Inject replacement values into the value.
              if( preg_match_all($regex['pattern'], $value, $matches, PREG_SET_ORDER) ) {

                // Loop through matches and replace each.
                foreach( $matches as $match ) {

                  // Replace the value.
                  $value = $regex['replacement']($value, $match);

                  // Only continue the loop if the value is still a string.
                  if( !is_string($value) ) break;

                }

              }

            }

          }

          // Evaluate the condition expression.
          $result = eval("return ($condition);");

          // If the expression evaluated successfully, then replace the value.
          if( $result ) {

            // Evaluate the value if indicated.
            if( $evaluate ) $value = eval("return ($value);");

            // Update the data with the new value.
            $data = array_set($data, $target, $value, true);

          }

        }

      }

    }

    // Return the mutated data.
    return $data;

  }

}

?>
