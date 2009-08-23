<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

interface LimeMockBehaviourInterface
{
  public function expect(LimeMockInvocationExpectation $invocation);

  public function invoke(LimeMockInvocation $invocation);

  public function verify();

  public function setExpectNothing();

  public function reset();
}