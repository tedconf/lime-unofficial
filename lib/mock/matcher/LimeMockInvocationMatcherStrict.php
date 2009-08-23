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
 * Requires an invokation to be called with the exact same parameter types
 * as the expected invokation.
 *
 * You have to pass the expected invokation to the constructor of this class.
 * If the parameter types do not match exactly, invoke() throws a
 * LimeMockInvocationException.
 *
 * @package    Lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 * @see        LimeMockInvocationMatcherInterface
 */
class LimeMockInvocationMatcherStrict implements LimeMockInvocationMatcherInterface
{
  protected
    $invocation = null;

  /**
   * Constructor.
   *
   * @param LimeMockInvocation $invocation  The expected invocation
   */
  public function __construct(LimeMockInvocation $invocation)
  {
    $this->invocation = $invocation;
  }

  /**
   * (non-PHPdoc)
   * @see mock/matcher/LimeMockInvocationMatcherInterface#invoke($invocation)
   */
  public function invoke(LimeMockInvocation $invocation)
  {
    if (!$this->invocation->equals($invocation, true))
    {
      throw new LimeMockInvocationMatcherException('should be called with the same parameter types');
    }
  }

  /**
   * (non-PHPdoc)
   * @see mock/matcher/LimeMockInvocationMatcherInterface#isInvokable()
   */
  public function isInvokable()
  {
    return true;
  }

  /**
   * (non-PHPdoc)
   * @see mock/matcher/LimeMockInvocationMatcherInterface#isSatisfied()
   */
  public function isSatisfied()
  {
    return true;
  }

  /**
   * (non-PHPdoc)
   * @see mock/matcher/LimeMockInvocationMatcherInterface#getMessage()
   */
  public function getMessage()
  {
    return '';
  }
}