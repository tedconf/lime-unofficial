<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockInvocationExpectation
{
  const
    COUNT_MATCHER     = 0,
    PARAMETER_MATCHER = 1;

  protected
    $invocation   = null,
    $matched      = false,
    $output       = null,
    $matchers     = array(),
    $returnValue  = null,
    $exception    = null,
    $callback     = null,
    $strict       = false,
    $verified     = false;

  public function __construct(LimeMockInvocation $invocation, LimeOutputInterface $output)
  {
    $this->invocation = $invocation;
    $this->output = $output;
  }

  public function __toString()
  {
    $string = $this->invocation.' was called';

    foreach ($this->matchers as $matcher)
    {
      // avoid trailing spaces if the message is empty
      $string = rtrim($string.' '.$matcher->getMessage());
    }

    return $string;
  }

  public function invoke(LimeMockInvocation $invocation)
  {
    try
    {
      foreach ($this->matchers as $matcher)
      {
        $matcher->invoke($invocation);
      }
    }
    catch (LimeMockInvocationMatcherException $e)
    {
      throw new LimeMockInvocationException($this->invocation, $e->getMessage());
    }

    if (!$this->verified && $this->isSatisfied())
    {
      list ($file, $line) = LimeTrace::findCaller('LimeMockInterface');

      $this->output->pass((string)$this, $file, $line);

      $this->verified = true;
    }

    if (!is_null($this->callback))
    {
      return call_user_func_array($this->callback, $invocation->getParameters());
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

  public function matches(LimeMockInvocation $invocation)
  {
    return $this->invocation->equals($invocation);
  }

  public function isInvokable()
  {
    $result = true;

    foreach ($this->matchers as $matcher)
    {
      $result = $result && $matcher->isInvokable();
    }

    return $result;
  }

  public function isSatisfied()
  {
    $result = true;

    foreach ($this->matchers as $matcher)
    {
      $result = $result && $matcher->isSatisfied();
    }

    return $result;
  }

  public function verify()
  {
    if (!$this->verified)
    {
      list ($file, $line) = LimeTrace::findCaller('LimeMockInterface');

      if ($this->isSatisfied())
      {
        $this->output->pass((string)$this, $file, $line);
      }
      else
      {
        $this->output->fail((string)$this, $file, $line);
      }

      $this->verified = true;
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

  public function any()
  {
    $this->matchers[self::COUNT_MATCHER] = new LimeMockInvocationMatcherAny();

    return $this;
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
    $this->matchers[self::PARAMETER_MATCHER] = new LimeMockInvocationMatcherStrict($this->invocation);

    return $this;
  }
}