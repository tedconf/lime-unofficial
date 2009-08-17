<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockInvocationMatcherTimes implements LimeMockInvocationMatcherInterface
{
  private
    $expected = 0,
    $actual   = 0;

  public function __construct($times)
  {
    $this->expected = $times;
  }

  public function invoke(LimeMockInvocation $invocation)
  {
    if ($this->actual < $this->expected)
    {
      $this->actual++;
    }
    else
    {
      if ($this->expected == 0)
      {
        throw new LimeMockInvocationMatcherException('should not be called');
      }
      else
      {
        $times = $this->getMessage();

        throw new LimeMockInvocationMatcherException(sprintf('should only be called %s', $times));
      }
    }
  }

  public function isInvokable()
  {
    return $this->actual < $this->expected;
  }

  public function isSatisfied()
  {
    return $this->actual >= $this->expected;
  }

  public function getMessage()
  {
    return $this->expected == 1 ? 'once' : $this->expected.' times';
  }
}