<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

abstract class LimeMockBehaviour implements LimeMockBehaviourInterface
{
  protected
    $invocations    = array(),
    $failOnVerify   = false,
    $expectNothing  = false,
    $strict         = false;

  public function expect(LimeMockExpectedInvocation $invocation)
  {
    $this->invocations[] = $invocation;
  }

  public function invoke(LimeMockInvocation $invocation)
  {
    if (!$this->failOnVerify && ($this->expectNothing || count($this->invocations) > 0))
    {
      throw new LimeAssertionException('Unexpected method call', $invocation);
    }
  }

  public function verify()
  {
    foreach ($this->invocations as $invocation)
    {
      $invocation->verify();
    }
  }

  public function setFailOnVerify()
  {
    $this->failOnVerify = true;
  }

  public function setExpectNothing()
  {
    $this->expectNothing = true;
  }

  public function setStrict()
  {
    $this->strict = true;
  }
}