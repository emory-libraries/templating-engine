<?php

namespace CustomHelpers;

trait LoopHelpers {

  // Repeat a block `x` number of times.
  public static function repeat( int $x, array $options ) {

    // Initialize the result.
    $result = '';

    // Repeat the block `x` number of times.
    for( $i = 0; $i < $x; $i++ ) {

      // Reveal data options.
      $data = array_merge([
        'index' => $i,
        'first' => $i === 0,
        'last' => $i === ($x - 1)
      ], array_get($options, 'data', []), array_get($options, 'hash', []));

      // Render the block, and capture its contents.
      $result .= $options['fn']($options['_this'], $data);

    }

    // Return the result.
    return $result;

  }

}

?>
