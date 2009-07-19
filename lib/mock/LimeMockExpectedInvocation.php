<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockExpectedInvocation
{
  protected
    $invocation   = null,
    $matched      = false,
    $output       = null,
    $matchers     = array(),
    $returnValue  = null,
    $exception    = null;

  public function __construct(LimeMockInvocation $invocation, LimeTest $output = null)
  {
    $this->invocation = $invocation;
    $this->output = $output;

    $this->matchers[] = new LimeMockInvocationMatcherParameters($invocation);
  }

  public function invoke()
  {
    if (!is_null($this->exception))
    {
      throw new $this->exception();
    }

    return $this->returnValue;
  }

  public function matches(LimeMockInvocation $invocation, $strict = false)
  {
    $matched = false;

    if ($this->invocation->getMethod() == $invocation->getMethod())
    {
      $matched = true;

      foreach ($this->matchers as $matcher)
      {
        $matched = $matched && $matcher->matches($invocation, $strict);
      }
    }

    return $matched;
  }

  public function isComplete()
  {
    $complete = true;

    foreach ($this->matchers as $matcher)
    {
      $complete = $complete && $matcher->isComplete();
    }

    return $complete;
  }

  public function verify()
  {
    if (is_null($this->output))
    {
      throw new BadMethodCallException('You must pass an instance of LimeTest to LimeMock::create() for verifying');
    }

    $messages = array();
    $valid = true;

    foreach ($this->matchers as $matcher)
    {
      $messages[] = $matcher->getMessage();
      $valid = $valid && $matcher->isComplete();
    }

    $this->output->ok($valid, implode(' ', $messages));
  }

  public function times($times)
  {
    $this->matchers[] = new LimeMockInvocationMatcherTimes($times);

    return $this;
  }

  public function returns($value)
  {
    $this->returnValue = $value;

    return $this;
  }

  public function throws($class)
  {
    $this->exception = $class;

    return $this;
  }
}