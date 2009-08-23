<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockRecordState
{
  protected
    $behaviour = null,
    $output = null;

  public function __construct(LimeMockBehaviourInterface $behaviour, LimeOutputInterface $output = null)
  {
    $this->behaviour = $behaviour;
    $this->output = $output;
  }

  public function invoke($class, $method, $parameters = LimeMockInvocation::ANY_PARAMETERS)
  {
    $invocation = new LimeMockInvocation($class, $method, $parameters);
    $invocation = new LimeMockInvocationExpectation($invocation, $this->output);

    $this->behaviour->expect($invocation);

    return $invocation;
  }

  /**
   * Configures the mock to expect exactly no method call.
   */
  public function setExpectNothing()
  {
    return $this->behaviour->setExpectNothing();
  }

  public function setFailOnVerify()
  {
    return $this->behaviour->setFailOnVerify();
  }

  public function setStrict()
  {
    return $this->behaviour->setStrict();
  }

  public function replay()
  {
    throw new BadMethodCallException('replay() is not supported');
  }

  public function reset()
  {
    return $this->behaviour->reset();
  }

  public function verify()
  {
    throw new BadMethodCallException('replay() must be called before verify()');
  }
}