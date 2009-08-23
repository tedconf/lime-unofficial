<?php

/*
 * This file is part of the Lime test framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * The state of the mock during record mode.
 *
 * During record mode, all methods that are called on the mock are turned into
 * invocation expectations. You may set modifiers on these expectations to
 * configure whether invocations should return values, throw exceptions etc.
 * in replay mode. See the description of LimeMockInvocationExpectation for
 * more information.
 *
 * @package    Lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 * @see        LimeMockInvocationExpectation
 */
class LimeMockRecordState implements LimeMockStateInterface
{
  protected
    $behaviour = null,
    $output = null;

  /**
   * Constructor.
   *
   * @param  LimeMockBehaviourInterface $behaviour  The behaviour on which this
   *                                                state operates
   * @param  LimeOutputInterface        $output     The output where failed and
   *                                                successful tests are written
   *                                                to.
   */
  public function __construct(LimeMockBehaviourInterface $behaviour, LimeOutputInterface $output)
  {
    $this->behaviour = $behaviour;
    $this->output = $output;
  }

  /**
   * (non-PHPdoc)
   * @see mock/LimeMockStateInterface#invoke($class, $method, $parameters)
   */
  public function invoke($class, $method, $parameters = LimeMockInvocation::ANY_PARAMETERS)
  {
    $invocation = new LimeMockInvocation($class, $method, $parameters);
    $invocation = new LimeMockInvocationExpectation($invocation, $this->output);

    $this->behaviour->expect($invocation);

    return $invocation;
  }

  /**
   * (non-PHPdoc)
   * @see mock/LimeMockStateInterface#setExpectNothing()
   */
  public function setExpectNothing()
  {
    return $this->behaviour->setExpectNothing();
  }

  /**
   * (non-PHPdoc)
   * @see mock/LimeMockStateInterface#verify()
   */
  public function verify()
  {
    throw new BadMethodCallException('replay() must be called before verify()');
  }
}