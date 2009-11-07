<?php

class LimeExceptionExpectation
{
  private
    $exception  = null,
    $file       = null,
    $line       = null;

  public function __construct($exception, $file, $line)
  {
    $this->exception = $exception;
    $this->file = $file;
    $this->line = $line;
  }

  public function getException()
  {
    return $this->exception;
  }

  public function getFile()
  {
    return $this->file;
  }

  public function getLine()
  {
    return $this->line;
  }
}