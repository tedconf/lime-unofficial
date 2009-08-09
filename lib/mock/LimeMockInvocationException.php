<?php

class LimeMockInvocationException extends Exception
{
  private
    $invocation           = null,
    $expectedInvocations  = array(),
    $pastInvocations      = array();

  public function __construct(LimeMockInvocation $invocation, array $expectedInvocations,
      array $pastInvocations)
  {
    $this->invocation = $invocation;
    $this->expectedInvocations = $expectedInvocations;
    $this->pastInvocations = $pastInvocations;

    parent::__construct('Unexpected method call: ' . $invocation);
  }
}