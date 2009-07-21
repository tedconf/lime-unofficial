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
    print serialize(array($method, $arguments))."\n";
  }

  public function plan($amount, $file)
  {
    $this->printCall('plan', array($amount, $file));
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

  public function warning($message, $file, $line)
  {
    $this->printCall('warning', array($message, $file, $line));
  }

  public function error($message, $file, $line)
  {
    $this->printCall('error', array($message, $file, $line));
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