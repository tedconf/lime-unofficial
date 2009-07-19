<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockOrderedBehaviour extends LimeMockBehaviour
{
  protected
    $cursor = 0;

  public function invoke(LimeMockInvocation $invocation)
  {
    $expectedInvocation = $this->invocations[$this->cursor];

    if ($expectedInvocation->matches($invocation, $this->strict))
    {
      if ($expectedInvocation->isComplete())
      {
        ++$this->cursor;
      }

      return $expectedInvocation->invoke();
    }

    parent::invoke($invocation);
  }
}