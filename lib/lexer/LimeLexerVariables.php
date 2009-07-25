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
 * This lexer includes all global variables that are not inside annotations,
 * except variables from the @Before scope, which are included as well.
 *
 * @package    lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class LimeLexerVariables extends LimeLexerAnnotationAware
{
  protected
    $includedAnnotations = array(),
    $variables = array();

  public function __construct(array $allowedAnnotations = array(), array $includedAnnotations = array())
  {
    parent::__construct($allowedAnnotations);

    $this->includedAnnotations = $includedAnnotations;
  }

  /**
   * (non-PHPdoc)
   * @see LimeLexer#process($text, $id)
   */
  protected function process($text, $id)
  {
    if ($id == T_VARIABLE && !$this->inClass() && !$this->inFunction()
        && (!$this->inAnnotation() || in_array($this->getCurrentAnnotation(), $this->includedAnnotations)))
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