<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockInvocationMatcherParameters
{
  protected
    $invocation = null;

  public function __construct(LimeMockInvocation $invocation)
  {
    $this->invocation = $invocation;
  }

  public function matches(LimeMockInvocation $invocation, $strict = false)
  {
    if ($this->invocation->equals($invocation, $strict))
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  public function isComplete()
  {
    return true;
  }

  public function getMessage()
  {
    return $this->invocation.' was called';
  }
}