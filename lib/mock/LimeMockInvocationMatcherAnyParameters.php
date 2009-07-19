<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockInvocationMatcherAnyParameters
{
  protected
    $complete   = false,
    $invocation = null;

  public function __construct(LimeMockInvocation $invocation)
  {
    $this->invocation = $invocation;
  }

  public function matches(LimeMockInvocation $invocation, $strict = false)
  {
    $this->complete = true;

    return true;
  }

  public function isComplete()
  {
    return $this->complete;
  }

  public function getMessage()
  {
    return 'The method '.$this->invocation.' was called with any parameters';
  }
}