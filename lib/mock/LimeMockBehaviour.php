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
    $options        = array(),
    $verified       = false,
    $invocations    = array(),
    $expectNothing  = false;

  public function __construct(array $options = array())
  {
    $this->options = array_merge(array(
      'strict'        =>  false,
      'nice'          =>  false,
      'no_exceptions' =>  false,
    ), $options);
  }

  public function expect(LimeMockInvocationExpectation $invocation)
  {
    $this->invocations[] = $invocation;

    if ($this->options['strict'])
    {
      $invocation->strict();
    }

    if ($this->options['nice'])
    {
      $invocation->any();
    }
    else
    {
      $invocation->once();
    }
  }

  public function invoke(LimeMockInvocation $invocation)
  {
    if (!$this->options['nice'] && !$this->verified && !$this->options['no_exceptions'] && ($this->expectNothing || count($this->invocations) > 0))
    {
      throw new LimeMockInvocationException($invocation, 'was not expected to be called');
    }
  }

  public function verify()
  {
    foreach ($this->invocations as $invocation)
    {
      $invocation->verify();
    }

    $this->verified = true;
  }

  public function setExpectNothing()
  {
    $this->expectNothing = true;
  }

  public function reset()
  {
    $this->invocations = array();
  }
}