<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockUnorderedBehaviour extends LimeMockBehaviour
{
  public function invoke(LimeMockInvocation $invocation)
  {
    foreach ($this->invocations as $expectedInvocation)
    {
      if ($expectedInvocation->matches($invocation))
      {
        return $expectedInvocation->invoke($invocation);
      }
    }

    parent::invoke($invocation);
  }
}