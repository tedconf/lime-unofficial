<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockInvocationMatcherBetween
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

  public function matches(LimeMockInvocation $invocation, $strict = false)
  {
    if ($this->actual < $this->end)
    {
      $this->actual++;

      return true;
    }
    else
    {
      return false;
    }
  }

  public function isComplete()
  {
    return $this->actual >= $this->start && $this->actual <= $this->end;
  }

  public function getMessage()
  {
    return sprintf('between %s and % times', $this->start, $this->end);
  }
}