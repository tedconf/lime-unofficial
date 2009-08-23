<?php

interface LimeMockStateInterface
{
  public function invoke($class, $method, $parameters = LimeMockInvocation::ANY_PARAMETERS);

  public function setExpectNothing();

  public function verify();
}