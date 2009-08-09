<?php

class LimeError extends Exception
{
  public function __construct($message, $file, $line)
  {
    parent::__construct($message);

    $this->file = $file;
    $this->line = $line;
  }
}