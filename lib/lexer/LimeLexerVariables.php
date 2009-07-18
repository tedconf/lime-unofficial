<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Extracts all global variables from a source file.
 *
 * @package    lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class LimeLexerVariables extends LimeLexer
{
  protected
    $variables = array();

  /**
   * (non-PHPdoc)
   * @see LimeLexer#process($text, $id)
   */
  protected function process($text, $id)
  {
    if ($id == T_VARIABLE && !$this->inClass() && !$this->inFunction())
    {
      $this->variables[] = $text;
    }
  }

  /**
   * (non-PHPdoc)
   * @see LimeLexer#getResult()
   */
  protected function getResult()
  {
    return array_unique($this->variables);
  }
}