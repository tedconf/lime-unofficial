<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeOutputRaw implements LimeOutputInterface
{
  protected function printCall($method, array $arguments = array())
  {
    foreach ($arguments as &$argument)
    {
      if (is_string($argument))
      {
        $argument = str_replace(array("\n", "\r"), array('\n', '\r'), $argument);
      }
    }

    print serialize(array($method, $arguments))."\n";
  }

  public function start($file)
  {
    $this->printCall('start', array($file));
  }

  public function plan($amount)
  {
    $this->printCall('plan', array($amount));
  }

  public function pass($message, $file, $line)
  {
    $this->printCall('pass', array($message, $file, $line));
  }

  public function fail($message, $file, $line, $error = null)
  {
    $this->printCall('fail', array($message, $file, $line, $error));
  }

  public function skip($message, $file, $line)
  {
    $this->printCall('skip', array($message, $file, $line));
  }

  public function todo($message, $file, $line)
  {
    $this->printCall('todo', array($message, $file, $line));
  }

  public function warning($message, $file, $line)
  {
    $this->printCall('warning', array($message, $file, $line));
  }

  public function error(Exception $exception)
  {
    $this->printCall('error', array($exception));
  }

  public function comment($message)
  {
    $this->printCall('comment', array($message));
  }

  public function flush()
  {
    $this->printCall('flush');
  }
}