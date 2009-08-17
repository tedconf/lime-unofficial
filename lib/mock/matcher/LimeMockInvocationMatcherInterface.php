<?php

interface LimeMockInvocationMatcherInterface
{
  public function invoke(LimeMockInvocation $invocation);

  public function isInvokable();

  public function isSatisfied();

  public function getMessage();
}