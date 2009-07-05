<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class sfLimeColorizer
{
  static public
    $styles = array();

  protected
    $forceColors = false;

  public function __construct($forceColors = false)
  {
    $this->forceColors = $forceColors;
  }

  public static function style($name, $options = array())
  {
    self::$styles[$name] = $options;
  }

  public function colorize($text = '', $parameters = array())
  {
    // disable colors if not supported (windows or non tty console)
    if (!$this->forceColors && (DIRECTORY_SEPARATOR == '\\' || !function_exists('posix_isatty') || !@posix_isatty(STDOUT)))
    {
      return $text;
    }

    static $options    = array('bold' => 1, 'underscore' => 4, 'blink' => 5, 'reverse' => 7, 'conceal' => 8);
    static $foreground = array('black' => 30, 'red' => 31, 'green' => 32, 'yellow' => 33, 'blue' => 34, 'magenta' => 35, 'cyan' => 36, 'white' => 37);
    static $background = array('black' => 40, 'red' => 41, 'green' => 42, 'yellow' => 43, 'blue' => 44, 'magenta' => 45, 'cyan' => 46, 'white' => 47);

    !is_array($parameters) && isset(self::$styles[$parameters]) and $parameters = self::$styles[$parameters];

    $codes = array();
    isset($parameters['fg']) and $codes[] = $foreground[$parameters['fg']];
    isset($parameters['bg']) and $codes[] = $background[$parameters['bg']];
    foreach ($options as $option => $value)
    {
      isset($parameters[$option]) && $parameters[$option] and $codes[] = $value;
    }

    return "\033[".implode(';', $codes).'m'.$text."\033[0m";
  }
}

sfLimeColorizer::style('ERROR', array('bg' => 'red', 'fg' => 'white', 'bold' => true));
sfLimeColorizer::style('INFO', array('fg' => 'green', 'bold' => true));
sfLimeColorizer::style('PARAMETER', array('fg' => 'cyan'));
sfLimeColorizer::style('COMMENT', array('fg' => 'yellow'));

sfLimeColorizer::style('GREEN_BAR', array('fg' => 'white', 'bg' => 'green', 'bold' => true));
sfLimeColorizer::style('RED_BAR', array('fg' => 'white', 'bg' => 'red', 'bold' => true));
sfLimeColorizer::style('INFO_BAR', array('fg' => 'cyan', 'bold' => true));