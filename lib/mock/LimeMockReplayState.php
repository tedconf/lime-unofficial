<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockReplayState
{
  protected
    $behaviour = null;

  public function __construct(LimeMockBehaviourInterface $behaviour)
  {
    $this->behaviour = $behaviour;
  }

  public function invoke($class, $method, array $parameters)
  {
    return $this->behaviour->invoke(new LimeMockInvocation($class, $method, $parameters));
  }

  /**
   * Configures the mock to expect exactly no method call.
   */
  public function setExpectNothing()
  {
    throw new BadMethodCallException('setExpectNothing() must be called before replay()');
  }

  public function setFailOnVerify()
  {
    throw new BadMethodCallException('setFailOnVerify() must be called before replay()');
  }

  public function setStrict()
  {
    throw new BadMethodCallException('setStrict() must be called before replay()');
  }

  public function replay()
  {
    throw new BadMethodCallException('replay() is not supported');
  }

  public function verify()
  {
    return $this->behaviour->verify();
  }
}