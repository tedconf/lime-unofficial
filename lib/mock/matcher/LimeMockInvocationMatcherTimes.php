<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockInvocationMatcherTimes
{
  private
    $expected = 0,
    $actual   = 0;

  public function __construct($times)
  {
    $this->expected = $times;
  }

  public function matches(LimeMockInvocation $invocation, $strict = false)
  {
    if ($this->actual < $this->expected)
    {
      $this->actual++;

      return true;
    }

    return false;
  }

  public function isComplete()
  {
    return $this->actual == $this->expected;
  }

  public function getMessage()
  {
    return $this->expected == 1 ? 'once' : $this->expected.' times';
  }
}