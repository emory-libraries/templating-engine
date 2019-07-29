<?php

/*
 * Conditional
 *
 * Constructs a conditional statement from a
 * given string expression and evaluates the
 * conditional statement to a Boolean value.
 */
class Conditional {

  // Evaluates a conditional expression given as a string.
  public static function expression( string $expression ) {

    // Initialize the result.
    $result = false;

    // Parse the conditional expression.
    $exp = self::parse($expression);

    // Verify that a recognized expression was given.
    if( $exp !== false ) {

      // Validate the conditional expression based on the operator
      switch( $exp['operator'] ) {
        case '===': return ($exp['a'] === $exp['b']);
        case '!==': return ($exp['a'] !== $exp['b']);
        case '==': return ($exp['a'] == $exp['b']);
        case '!=': return ($exp['a'] != $exp['b']);
        case '<>': return ($exp['a'] <> $exp['b']);
        case '<=>': return ($exp['a'] <=> $exp['b']);
        case '<': return ($exp['a'] < $exp['b']);
        case '<=': return ($exp['a'] <= $exp['b']);
        case '>': return ($exp['a'] > $exp['b']);
        case '>=': return ($exp['a'] >= $exp['b']);
      }

    }

    // Return the result.
    return $result;

  }

  // Parses conditional parts given a string expression.
  public static function parse( string $expression ) {

    // Initialize the result.
    $result = false;

    // Verify that a recognized expression was given.
    if( preg_match('/^(?P<a>.+?) (?P<o>[=|!]==?|<=?>|<=?|>=?) (?P<b>.+)$/', $expression, $matches) ) {

      // Capture the expression's parts.
      $result = [
        'a' => trim($matches['a'], '\'" '),
        'b' => trim($matches['b'], '\'" '),
        'operator' => $matches['o']
      ];

    }

    // Return the result.
    return $result;

  }

}

?>
