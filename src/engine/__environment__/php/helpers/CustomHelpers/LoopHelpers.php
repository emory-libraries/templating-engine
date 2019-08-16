<?php

namespace CustomHelpers;

trait LoopHelpers {

  // Repeat a block `x` number of times.
  public static function repeat( int $n, array $options ) {

    // Initialize the result.
    $result = '';

    // Repeat the block `x` number of times.
    for( $i = 0; $i < $n; $i++ ) {

      // Reveal data options.
      $data = array_merge(array_get($options, 'data', []), array_get($options, 'hash', []), [
        'index' => $i,
        'first' => $i === 0,
        'last' => $i === ($n - 1)
      ]);

      // Render the block, and capture its contents.
      $result .= $options['fn']($options['_this'], ['data' => $data]);

    }

    // Return the result.
    return $result;

  }

}

?>
