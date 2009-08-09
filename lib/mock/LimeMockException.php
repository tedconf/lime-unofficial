<?php

class LimeMockException extends Exception
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

    parent::__construct('Unexpected call: ' . $invocation);
  }

  public function getInvocation()
  {
    return $this->invocation;
  }

  public function getExpectedInvocations()
  {
    return $this->expectedInvocations;
  }

  public function getPastInvocations()
  {
    return $this->pastInvocations;
  }
}