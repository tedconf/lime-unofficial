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
 * Wrapper class for PHP errors.
 *
 * This class is inspired by PHPUnit's class PHPUnit_Framework_Error.
 *
 * @package    lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class LimeError extends Exception
{
  /**
   * Constructor.
   *
   * @param string  $message
   * @param integer $code
   * @param string  $file
   * @param integer $line
   * @param array   $trace
   */
  public function __construct($message, $code, $file, $line, $trace)
  {
    parent::__construct($message, $code);

    $this->file = $file;
    $this->line = $line;
    $this->trace  = $trace;
  }
}