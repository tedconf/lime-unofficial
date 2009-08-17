<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockInvocationMatcherBetween implements LimeMockInvocationMatcherInterface
{
  private
    $start  = 0,
    $end    = 0,
    $actual = 0;

  public function __construct($start, $end)
  {
    if ($start > $end)
    {
      $this->start = $end;
      $this->end = $start;
    }
    else
    {
      $this->start = $start;
      $this->end = $end;
    }
  }

  public function invoke(LimeMockInvocation $invocation)
  {
    if ($this->actual < $this->end)
    {
      $this->actual++;
    }
    else
    {
      throw new LimeMockInvocationMatcherException(sprintf('should only be called %s', $this->getMessage()));
    }
  }

  public function isInvokable()
  {
    return $this->actual < $this->end;
  }

  public function isSatisfied()
  {
    return $this->actual >= $this->start && $this->actual <= $this->end;
  }

  public function getMessage()
  {
    return sprintf('between %s and % times', $this->start, $this->end);
  }
}