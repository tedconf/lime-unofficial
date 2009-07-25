<?php

class LimeExpectedException
{
  private
    $class = null,
    $code = null,
    $file = null,
    $line = null;

  public static function create(Exception $e = null)
  {
    if (!is_null($e))
    {
      return new self(get_class($e), $e->getCode(), $e->getFile(), $e->getLine());
    }
    else
    {
      return new self();
    }
  }

  public function __construct($class = null, $code = null, $file = null, $line = null)
  {
    $this->class = $class;
    $this->code = $code;
    $this->file = $file;
    $this->line = $line;
  }

  public function equals(LimeExpectedException $e)
  {
    return $this->class == $e->class && $this->code == $e->code;
  }

  public function getFile()
  {
    return $this->file;
  }

  public function getLine()
  {
    return $this->line;
  }

  public function getClass()
  {
    return $this->class;
  }

  public function getCode()
  {
    return $this->code;
  }

  public function __toString()
  {
    if (is_null($this->class))
    {
      return 'none';
    }
    else if (is_null($this->code))
    {
      return $this->class;
    }
    else
    {
      return sprintf("%s (%s)", $this->class, var_export($this->code, true));
    }
  }
}