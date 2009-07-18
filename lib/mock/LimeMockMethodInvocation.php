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
 * Represents the invocation of a class or object method with a set of
 * parameters.
 *
 * This class is used internally by LimeMockControl to track the method
 * invocations on mock objects.
 *
 * @package    lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class LimeMockMethodInvocation
{
  protected
    $method     = null,
    $parameters = array();

  /**
   * Constructor.
   *
   * @param string $method
   * @param array $parameters
   */
  public function __construct($method, array $parameters)
  {
    $this->method = $method;
    $this->parameters = $parameters;
  }

  /**
   * Returns a unique hash code.
   *
   * @return string A hash with a length of 32 characters.
   */
  public function hashCode()
  {
    return md5(serialize($this));
  }

  /**
   * Returns a string representation of the method call invocation.
   *
   * The result looks like a method call in PHP source code.
   *
   * Example:
   * <code>
   * $invocation = new LimeMockMethodInvocation('doSomething', array(1, 'foobar'));
   * print $invocation;
   *
   * // => "doSomething(1, 'foobar')"
   * </code>
   *
   * @return string
   */
  public function __toString()
  {
    return sprintf('%s(%s)', $this->method, implode(', ', $this->parameters));
  }
}