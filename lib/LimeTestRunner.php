<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Runs a set of test methods.
 *
 * You can add different types of callbacks to a test runner. The most important
 * type is the "test" callback. These callbacks are added by calling addTest().
 * All "test" callbacks are executed upon calling run().
 *
 * The other callback types are called before or after the "test" callbacks:
 *
 *    - "before all": Called once before all tests
 *    - "after all": Called once after all tests
 *    - "before": Called before each test
 *    - "after": Called after each test
 *
 * These callbacks are added by calling addBeforeAll(), addAfterAll(),
 * addBefore() and addAfter(). You can add multiple callbacks for each type.
 * Callbacks are called in the same order in which they are added.
 *
 * @package    lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class LimeTestRunner
{
  protected
    $beforeAllCallbacks = array(),
    $afterAllCallbacks  = array(),
    $beforeCallbacks    = array(),
    $afterCallbacks     = array(),
    $testCallbacks      = array();

  /**
   * Runs all registered callbacks.
   */
  public function run()
  {
    foreach ($this->beforeAllCallbacks as $callback)
    {
      call_user_func($callback);
    }

    foreach ($this->testCallbacks as $testCallback)
    {
      foreach ($this->beforeCallbacks as $callback)
      {
        call_user_func($callback);
      }

      call_user_func($testCallback);

      foreach ($this->afterCallbacks as $callback)
      {
        call_user_func($callback);
      }
    }

    foreach ($this->afterAllCallbacks as $callback)
    {
      call_user_func($callback);
    }
  }

  /**
   * Adds a callable that is called once before all tests.
   *
   * @param  callable $callback
   * @throws InvalidArgumentException  If the argument is no callbale
   */
  public function addBeforeAll($callback)
  {
    $this->assertIsCallable($callback);
    $this->beforeAllCallbacks[] = $callback;
  }

  /**
   * Adds a callable that is called once after all tests.
   *
   * @param  callable $callback
   * @throws InvalidArgumentException  If the argument is no callbale
   */
  public function addAfterAll($callback)
  {
    $this->assertIsCallable($callback);
    $this->afterAllCallbacks[] = $callback;
  }

  /**
   * Adds a callable that is called before each test.
   *
   * @param  callable $callback
   * @throws InvalidArgumentException  If the argument is no callbale
   */
  public function addBefore($callback)
  {
    $this->assertIsCallable($callback);
    $this->beforeCallbacks[] = $callback;
  }

  /**
   * Adds a callable that is called after each test.
   *
   * @param  callable $callback
   * @throws InvalidArgumentException  If the argument is no callbale
   */
  public function addAfter($callback)
  {
    $this->assertIsCallable($callback);
    $this->afterCallbacks[] = $callback;
  }

  /**
   * Adds a test callable.
   *
   * @param  callable $callback
   * @throws InvalidArgumentException  If the argument is no callbale
   */
  public function addTest($callback)
  {
    $this->assertIsCallable($callback);
    $this->testCallbacks[] = $callback;
  }

  /**
   * Asserts that the given argument is a callable.
   *
   * @param  mixed $callable
   * @throws InvalidArgumentException  If the argument is no callbale
   */
  private function assertIsCallable($callable)
  {
    if (!is_callable($callable))
    {
      throw new InvalidArgumentException('The given Argument must be a callable.');
    }
  }

}