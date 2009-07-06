<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class sfLimeOutput
{
  public
    $colorizer = null;

  public function __construct($forceColors = false)
  {
    if (sfLimeColorizer::isSupported() || $forceColors)
    {
      $this->colorizer = new sfLimeColorizer();
    }
  }

  protected function colorize($text, $style)
  {
    return is_null($this->colorizer) ? $text : $this->colorizer->colorize($text, $style);
  }

  public function diag()
  {
    $messages = func_get_args();
    foreach ($messages as $message)
    {
      echo $this->colorize('# '.join("\n# ", (array) $message), 'COMMENT')."\n";
    }
  }

  public function comment($message)
  {
    echo $this->colorize(sprintf('# %s', $message), 'COMMENT')."\n";
  }

  public function info($message)
  {
    echo $this->colorize(sprintf('> %s', $message), 'INFO_BAR')."\n";
  }

  public function error($message)
  {
    echo $this->colorize(sprintf(' %s ', $message), 'RED_BAR')."\n";
  }

  public function echoln($message, $colorizerParameter = null, $colorize = true)
  {
    if ($colorize)
    {
      $message = preg_replace('/(?:^|\.)((?:not ok|dubious) *\d*)\b/e', '$this->colorize(\'$1\', \'ERROR\')', $message);
      $message = preg_replace('/(?:^|\.)(ok *\d*)\b/e', '$this->colorize(\'$1\', \'INFO\')', $message);
      $message = preg_replace('/"(.+?)"/e', '$this->colorize(\'$1\', \'PARAMETER\')', $message);
      $message = preg_replace('/(\->|\:\:)?([a-zA-Z0-9_]+?)\(\)/e', '$this->colorize(\'$1$2()\', \'PARAMETER\')', $message);
    }

    echo ($colorizerParameter ? $this->colorize($message, $colorizerParameter) : $message)."\n";
  }

  public function greenBar($message)
  {
    echo $this->colorize($message.str_repeat(' ', 71 - min(71, strlen($message))), 'GREEN_BAR')."\n";
  }

  public function redBar($message)
  {
    echo $this->colorize($message.str_repeat(' ', 71 - min(71, strlen($message))), 'RED_BAR')."\n";
  }
}