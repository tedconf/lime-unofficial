<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

interface LimeOutputInterface
{
  public function start($file);

  public function plan($amount);

  public function pass($message, $file, $line);

  public function fail($message, $file, $line, $error = null);

  public function skip($message, $file, $line);

  public function warning($message, $file, $line);

  public function error(Exception $exception);

  public function info($message);

  public function comment($message);

  public function flush();
}