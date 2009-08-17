<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockInvocationMatcherStrict implements LimeMockInvocationMatcherInterface
{
  protected
    $invocation = null;

  public function __construct(LimeMockInvocation $invocation)
  {
    $this->invocation = $invocation;
  }

  public function invoke(LimeMockInvocation $invocation)
  {
    if (!$this->invocation->equals($invocation, true))
    {
      throw new LimeMockInvocationMatcherException('should be called with the same parameter types');
    }
  }

  public function isInvokable()
  {
    return true;
  }

  public function isSatisfied()
  {
    return true;
  }

  public function getMessage()
  {
    return '';
  }
}