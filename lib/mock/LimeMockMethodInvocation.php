<?php

class LimeMockMethodInvocation
{
  protected
    $method     = null,
    $parameters = array();

  public function __construct($method, array $parameters)
  {
    $this->method = $method;
    $this->parameters = $parameters;
  }

  public function hashCode()
  {
    return md5(serialize($this));
  }

  public function __toString()
  {
    return sprintf('%s(%s)', $this->method, implode(', ', $this->parameters));
  }
}