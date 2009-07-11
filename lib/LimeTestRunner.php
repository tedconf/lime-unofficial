<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeTestRunner
{
  protected
    $beforeAllCallbacks = array(),
    $afterAllCallbacks  = array(),
    $beforeCallbacks    = array(),
    $afterCallbacks     = array(),
    $testCallbacks      = array();

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

  public function addBeforeAll($callback)
  {
    $this->beforeAllCallbacks[] = $callback;
  }

  public function addAfterAll($callback)
  {
    $this->afterAllCallbacks[] = $callback;
  }

  public function addBefore($callback)
  {
    $this->beforeCallbacks[] = $callback;
  }

  public function addAfter($callback)
  {
    $this->afterCallbacks[] = $callback;
  }

  public function addTest($callback)
  {
    $this->testCallbacks[] = $callback;
  }

}