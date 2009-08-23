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
    if (array_key_exists($this->cursor, $this->invocations))
    {
      $invocationExpectation = $this->invocations[$this->cursor];

      if ($invocationExpectation->matches($invocation) && $invocationExpectation->isInvokable())
      {
        return $invocationExpectation->invoke($invocation);
      }
      else if ($invocationExpectation->isSatisfied())
      {
        $this->cursor++;

        return $this->invoke($invocation);
      }
    }

    parent::invoke($invocation);
  }
}