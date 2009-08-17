<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockInvocationMatcherAtLeastOnce implements LimeMockInvocationMatcherInterface
{
  private
    $actual   = 0;

  public function invoke(LimeMockInvocation $invocation)
  {
    $this->actual++;

    return true;
  }

  public function isInvokable()
  {
    return true;
  }

  public function isSatisfied()
  {
    return $this->actual >= 1;
  }

  public function getMessage()
  {
    return 'at least once';
  }
}