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
 * Thrown when assertions fail.
 *
 * @package    lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class LimeAssertionException extends Exception
{
  const
    NONE            = '__LIME_NONE__';

  protected
    $actualValue    = null,
    $expectedValue  = null;

  /**
   * Constructor.
   *
   * @param string $message
   * @param mixed  $actualValue
   * @param mixed  $expectedValue
   */
  public function __construct($message, $actualValue = self::NONE, $expectedValue = self::NONE, $file = null, $line = null)
  {
    $message = trim($message, '.');

    if ($actualValue !== self::NONE)
    {
      if ($expectedValue === self::NONE)
      {
        $message = $message.': '.$actualValue;
      }
      else
      {
        $message .= '. Got: '.$actualValue.'. Expected: '.$expectedValue;
      }
    }

    parent::__construct($message);

    $this->actualValue = $actualValue;
    $this->expectedValue = $expectedValue;

    if (!is_null($file))
    {
      $this->file = $file;
    }

    if (!is_null($line))
    {
      $this->line = $line;
    }
  }

  /**
   * Returns the actual value of the assertion.
   *
   * @return mixed
   */
  public function getActualValue()
  {
    return $this->actualValue;
  }

  /**
   * Returns the expected value of the assertion.
   *
   * @return mixed
   */
  public function getExpectedValue()
  {
    return $this->expectedValue;
  }
}