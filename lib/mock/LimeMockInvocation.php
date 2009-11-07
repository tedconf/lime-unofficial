<?php

/*
 * This file is part of the Lime test framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Bernhard Schussek <bschussek@gmail.com>
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
 * @package    Lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class LimeMockInvocation
{
  const
    ANY_PARAMETERS  = '...';

  protected
    $method         = null,
    $parameters     = array();

  /**
   * Constructor.
   *
   * @param string        $method      The method name
   * @param array|string  $parameters  The method parameters or ANY_PARAMETERS
   */
  public function __construct($class, $method, $parameters = array())
  {
    $this->class = $class;
    $this->method = $method;
    $this->parameters = $parameters;
  }

  /**
   * Returns the class name.
   *
   * @return string
   */
  public function getClass()
  {
    return $this->class;
  }

  /**
   * Returns the method name.
   *
   * @return string
   */
  public function getMethod()
  {
    return $this->method;
  }

  /**
   * Returns the method parameters.
   *
   * @return array|string   The parameter array or ANY_PARAMETERS
   */
  public function getParameters()
  {
    return $this->parameters;
  }

  /**
   * Returns the parameter at the given index.
   *
   * @param  integer $index
   * @return mixed
   */
  public function getParameter($index)
  {
    if ($index >= count($this->parameters))
    {
      throw new OutOfRangeException(sprintf('The parameter %s does not exist', $index));
    }

    return $this->parameters[$index];
  }

  /**
   * Returns whether this object equals the given invocation.
   *
   * @param  LimeMockInvocation  $invocation  The compared invocation
   * @param  boolean             $strict      Whether to use strict parameter
   *                                          comparison
   * @return boolean                          TRUE if the objects are equal
   */
  public function equals(LimeMockInvocation $invocation, $strict = false)
  {
    if ($this->method != $invocation->method || $this->class != $invocation->class)
    {
      return false;
    }
    else if ($this->parameters == self::ANY_PARAMETERS)
    {
      return true;
    }
    else
    {
      $exp1 = LimeTester::create($this->parameters);
      $exp2 = LimeTester::create($invocation->parameters);

      try
      {
        if ($strict)
        {
          $exp1->assertSame($exp2);
        }
        else
        {
          $exp1->assertEquals($exp2);
        }

        return true;
      }
      catch (LimeAssertionFailedException $e)
      {
        return false;
      }
    }
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
    $parameters = $this->parameters;

    if (is_array($parameters))
    {
      foreach ($parameters as $key => $value)
      {
        if (is_string($value))
        {
          $value = str_replace(array("\0", "\n", "\t", "\r"), array('\0', '\n', '\t', '\r'), $value);
          $value = strlen($value) > 30 ? substr($value, 0, 30).'...' : $value;
          $parameters[$key] = '"'.$value.'"';
        }
        else if (is_object($value))
        {
          $parameters[$key] = get_class($value);
        }
        else if (is_array($value))
        {
          $parameters[$key] = 'array';
        }
        else
        {
          $parameters[$key] = var_export($value, true);
        }
      }
    }

    return sprintf('%s(%s)', $this->method, implode(', ', (array)$parameters));
  }
}