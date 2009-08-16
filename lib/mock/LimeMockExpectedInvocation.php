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
  const
    PARAMETER_MATCHER = 0,
    COUNT_MATCHER     = 1;

  protected
    $invocation   = null,
    $matched      = false,
    $output       = null,
    $matchers     = array(),
    $returnValue  = null,
    $exception    = null,
    $callback     = null,
    $strict       = false;

  public function __construct(LimeMockInvocation $invocation, LimeOutputInterface $output = null)
  {
    $this->invocation = $invocation;
    $this->output = $output;

    $this->matchers[self::PARAMETER_MATCHER] = new LimeMockInvocationMatcherParameters($invocation);

    $this->atLeastOnce();
  }

  public function invoke(array $parameters)
  {
    if (!is_null($this->callback))
    {
      return call_user_func_array($this->callback, $parameters);
    }

    if (!is_null($this->exception))
    {
      if (is_string($this->exception))
      {
        throw new $this->exception();
      }
      else
      {
        throw $this->exception;
      }
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
        $matched = $matched && $matcher->matches($invocation, $strict || $this->strict);
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

    list ($file, $line) = LimeTrace::findCaller('LimeMockInterface');
    $message = implode(' ', $messages);

    if ($valid)
    {
      $this->output->pass($message, $file, $line);
    }
    else
    {
      $this->output->fail($message, $file, $line);
    }
  }

  public function times($times)
  {
    $this->matchers[self::COUNT_MATCHER] = new LimeMockInvocationMatcherTimes($times);

    return $this;
  }

  public function once()
  {
    return $this->times(1);
  }

  public function never()
  {
    return $this->times(0);
  }

  public function atLeastOnce()
  {
    $this->matchers[self::COUNT_MATCHER] = new LimeMockInvocationMatcherAtLeastOnce();

    return $this;
  }

  public function between($start, $end)
  {
    $this->matchers[self::COUNT_MATCHER] = new LimeMockInvocationMatcherBetween($start, $end);

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

  public function callback($callback)
  {
    if (!is_callable($callback))
    {
      throw new InvalidArgumentException('The given argument is no callable');
    }

    $this->callback = $callback;

    return $this;
  }

  public function strict()
  {
    $this->strict = true;

    return $this;
  }
}