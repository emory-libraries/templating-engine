<?php

/*
 * Feed
 *
 * Fetches and parses RSS and/or JSON feeds from
 * external sources.
 */
class Feed {

  // The feed's source URL.
  public $url;

  // The feed's parsed data.
  public $data;

  // Indicates the feed type.
  public $type = null;

  // Indicates the feed's version, if applicable.
  public $version = null;

  // Identifies namespaces that correlate to different types of feed.
  protected static $xmlns = [
    'atom' => 'http://www.w3.org/2005/Atom'
  ];

  // Defines regular expressions for finding feed metadata for RSS/RDF feeds.
  protected static $regex = '/\<(rdf|rss)[:\s\S]*?(?=\s|\>)([\s\S]*?)?\>/';

  // Constructor
  function __construct( string $url ) {

    // Save the feed's source URL.
    $this->url = $url;

    // Identify the feed's type.
    $type = static::type($this->url);

    // Save the feed's type and version.
    $this->type = $type['type'];
    $this->version = $type['version'];

    // Parse the feed, and save its data.
    $this->data = static::parse($this->url, $this->type);

  }

  // Parse a feed given its ULR and feed type.
  public static function parse( string $url, string $type ) {

    // Get the feed's contents.
    $contents = file_get_contents($url);

    // If the type is JSON, then parse the JSON feed, and return it as an array.
    if( $type === 'json' ) return json_decode($contents, true);

    // Parse the feed contents as XML.
    $xml = new SimpleXMLElement($contents);

    // Get namespaces within the XML.
    $namespaces = $xml->getNamespaces(true);

    // Convert the XML into an array of feed data.
    $data = object_to_array($xml);

    // Initialize a helper method for getting all namespaced values within the feed's XML.
    $xml_get_namespaced = function( SimpleXMLElement $xml ) use ($namespaces) {

      // Initialize the result.
      $result = [];

      // Initializes a helper method for counting the number of instances of a node in a set.
      $count = function( SimpleXmlElement $xml, string $name, string $ns = null ) {

        // Get all nodes by name.
        $nodes = isset($ns) ? $xml->children($ns, true) : $xml->children();

        // Initialize a counter.
        $count = 0;

        // Count the nodes, and increment the counter.
        foreach( $nodes as $node ) {

          // Infer the node's name.
          $n = (isset($ns) ? "$ns:{$node->getName()}" : $node->getName());

          // Only count nodes with the given name.
          if( $n == $name ) $count++;

        }

        // Return the number of nodes counted.
        return $count;

      };

      // Initialize a helper method for saving child node data.
      $save_node = function( SimpleXmlElement $xml, string $ns, $parent = null ) use (&$save_node, $count) {

        // Initialize the result.
        $result = [];

        // Keep track of repetitive key indices.
        $index = [];

        // Look for namespaced child nodes.
        $nodes = $xml->children($ns, true);

        // Save each namespaced child node found.
        foreach( $nodes as $node ) {

          // Get the node name.
          $name = "$ns:{$node->getName()}";

          // Get the key and value for the current node.
          $key = $parent.(isset($parent) ? ".$name" : $name);
          $value = trim((string) $node);

          // Convert empty values to arrays.
          if( !isset($value) or $value === '' ) $value = [];

          // For repetitive keys, keep track of the key index, add an index into the key name.
          if( $count($xml, $name, $ns) > 1 ) {

            // Initialize the index as needed.
            if( !array_key_exists($name, $index) ) $index[$name] = -1;

            // Increment the index.
            $i = ++$index[$name];

            // Overwrite the key.
            $key = $parent.(isset($parent) ? ".$name.$i" : "$name.$i");

          }

          // Save the key and value.
          //$result = array_merge_recursive($result, [$key => $value]);
          $result[$key] = $value;

          // Recursively locate nested nodes.
          $result = array_merge($result, $save_node($node, $ns, $key));

        }

        // Get all XML node children.
        $children = $xml->children();

        // Continue to locate namespaced nodes within the XML's children.
        foreach( $children as $child ) {

          // Get the node name.
          $name = $child->getName();

          // Get the key for the current node.
          $key = $parent.(isset($parent) ? ".$name" : $name);

          // For repetitive keys, keep track of the key index, add an index into the key name.
          if( $count($xml, $name) > 1 ) {

            // Initialize the index as needed.
            if( !array_key_exists($name, $index) ) $index[$name] = -1;

            // Increment the index.
            $i = ++$index[$name];

            // Overwrite the key.
            $key = $parent.(isset($parent) ? ".$name.$i" : "$name.$i");

          }

          // Recursively locate namespaced attributes.
          $result = array_merge($result, $save_node($child, $ns, $key));

        }

        // Return the result.
        return $result;

      };

      // Initialize a helper method for saving child attribute data.
      $save_attr = function( SimpleXmlElement $xml, string $ns, $parent = null ) use (&$save_attr, $count) {

        // Initialize the result.
        $result = [];

        // Keep track of repetitive key indices.
        $index = [];

        // Look for prefixed attributes on the current XML node.
        $attributes = $xml->attributes($ns, true);

        // Save each attribute found.
        foreach( $attributes as $attribute ) {

          // Get the attribute name.
          $name = "$ns:{$attribute->getName()}";

          // Get the key and value for the current attribute.
          $key = $parent.(isset($parent) ? ".@attributes.$name" : "@$name");
          $value = trim((string) $attribute);

          // Save the attribute's xpath.
          $result[$key] = $value;

        }

        // Get the XML node's children.
        $children = $xml->children();

        // Continue to locate attributes on the XML node's children.
        foreach( $children as $child ) {

          // Get the attribute name.
          $name = $child->getName();

          // Get the key for the current attribute.
          $key = $parent.(isset($parent) ? ".$name" : $name);

          // For repetitive keys, keep track of the key index, add an index into the key name.
          if( $count($xml, $name) > 1 ) {

            // Initialize the index as needed.
            if( !array_key_exists($name, $index) ) $index[$name] = -1;

            // Increment the index.
            $i = ++$index[$name];

            // Overwrite the key.
            $key = $parent.(isset($parent) ? ".$name.$i" : "$name.$i");

          }

          // Recursively locate namespaced attributes.
          $result = array_merge($result, $save_attr($child, $ns, $key));

        }

        // Get the XML node's namespaced children.
        $nodes = $xml->children($ns, true);

        // Continue to locate attributes on the namespaced XML nodes.
        foreach( $nodes as $node ) {

          // Get the attribute name.
          $name = "$ns:{$node->getName()}";

          // Get the key for the current attribute.
          $key = $parent.(isset($parent) ? ".$name" : $name);

          // For repetitive keys, keep track of the key index, add an index into the key name.
          if( $count($xml, $name, $ns) > 1 ) {

            // Initialize the index as needed.
            if( !array_key_exists($name, $index) ) $index[$name] = -1;

            // Increment the index.
            $i = ++$index[$name];

            // Overwrite the key.
            $key = $parent.(isset($parent) ? ".$name.$i" : "$name.$i");

          }

          // Recursively locate namespaced attributes.
          $result = array_merge($result, $save_attr($node, $ns, $key));

        }

        // Return the result.
        return $result;

      };

      // Look for namespaced child nodes.
      foreach( $namespaces as $prefix => $namespace ) {

        // Ignore empty namespaces.
        if( !isset($prefix) or $prefix === '' or is_int($prefix) ) continue;

        // Save namespaced node data.
        $result = array_merge($result, $save_node($xml, $prefix));

        // Save namespaced attribute data.
        $result = array_merge($result, $save_attr($xml, $prefix));

      }

      // Expand the result.
      $result = array_expand($result);

      // Return the result.
      return $result;

    };

    // Get all namespaced XML data, and merge it back into the data array.
    $data = array_merge_exact_recursive($data, $xml_get_namespaced($xml));

    // Return the parsed feed data.
    return $data;

  }

  // Extract the XML metadata from a feed.
  public static function metadata( string $xml ) {

    // Attempt to locate the string's metadata.
    preg_match(static::$regex, $xml, $metadata);

    // If no metadata could be found, then return false.
    if( !$metadata or empty($metadata) ) return false;

    // Otherwise, convert the metadata into an array for easier interpretation.
    return array_merge(array_reduce(explode(' ', trim($metadata[2])), function($data, $attribute) {

      // Split the attribute's key and value.
      $attribute = array_map(function($value) {

        // Remove quotes and whitespace from the attribute value.
        return strtr($value, ['"' => '', "'" => '', ' ' => '']);

      }, explode('=', $attribute));

      // Group like attributes together.
      if( strpos($attribute[0], ':') !== false ) {

        // Get the attribute key parts.
        $key = explode(':', $attribute[0]);

        // Look for the attribute's base key, and if it exists, convert it to an array.
        if( array_key_exists($key[0], $data) ) {

          // Only convert keys that are not already in array form.
          if( !is_array($data[$key[0]]) ) $data[$key[0]] = [$data[$key[0]]];

        }

        // Otherwise, generate the base key now as an array of values.
        else $data[$key[0]] = [];

        // Then, save the value under the designated key.
        $data[$key[0]][$key[1]] = $attribute[1];

      }

      // Otherwise, set the attribute value.
      else {

        // Check to see if the key already exists within the data.
        if( array_key_exists($attribute[0], $data) ) {

          // If th intended key is an array, then add to it.
          if( is_array($data[$attribute[0]]) ) $data[$attribute[0]][] = $attribute[1];

          // Otherwise, convert it to an array, then add to it.
          else $data[$attribute[0]] = [$data[$attribute[0]], $attribute[1]];

        }

        // Otherwise, simply assign the new value.
        else $data[$attribute[0]] = $attribute[1];

      }

      // Continue reducing.
      return $data;

    }, []), [
      'type' => $metadata[1]
    ]);

  }

  // Identifies the type of feed given by URL.
  public static function type( string $url ) {

    // Initialize the result.
    $result = [
      'type' => null,
      'version' => null
    ];

    // Fetch the contents of the URL.
    $contents = file_get_contents($url);

    // Check to see if the contents is JSON by attempting to decode it.
    json_decode($contents);

    // If the contents was able to be decoded, then it's JSON.
    if( json_last_error() === JSON_ERROR_NONE ) $result['type'] = 'json';

    // Otherwise, check to see if the content type is RSS or RDF.
    else if( ($metadata = static::metadata($contents)) !== false ) {

      // Set the feed type and version if given.
      $result['type'] = $metadata['type'];
      $result['version'] = array_get($metadata, 'version', $metadata['type'] === 'rss' ? '1.0' : null);

      // Determine if the feed type uses a special RSS schema, and if so, overwrite the feed type.
      if( array_key_exists('xmlns', $metadata) ) {

        // Get the RSS feed's namespaces.
        $xmlns = array_get($metadata, 'xmlns');

        // If the feed uses multiple namespaces, determine if any recognized namespaces are used.
        if( (is_array($xmlns) and count($intersection = array_intersect(static::$xmlns, $xmlns)) > 0) ) {

          // Identify the namespaces that were found.
          $found = array_values($intersection);

          // Cross-reference the namespaces wither their recognized namespace index.
          $index = array_map(function($namespace) {

            // Get the index of the namespace in the list.
            return array_search($namespace, array_values(static::$xmlns));

          }, $found);

          // Overwrite the feed type.
          $result['type'] = array_keys(static::$xmlns)[$index[0]];

        }

        // Otherwise, if the feed only uses a single namespace, determine if the namespace is recognized.
        else if( in_array($xmlns, array_values(static::$xmlns)) ) {

          // Overwrite the feed type.
          $result['type'] = static::$xmlns[array_search($xmlns, array_values(static::$xmlns))];

        }

      }

    }

    // Return the result.
    return $result;

  }

}

?>
