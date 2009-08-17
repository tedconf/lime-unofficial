<?php

class LimeMockInvocationException extends Exception
{
  private
    $invocation   = null;

  public function __construct(LimeMockInvocation $invocation, $message)
  {
    parent::__construct($invocation.' '.$message);

    $this->invocation = $invocation;
  }

  public function getInvocation()
  {
    return $this->invocation;
  }
}