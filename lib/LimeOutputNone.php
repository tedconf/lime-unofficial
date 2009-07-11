<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeOutputNone extends LimeOutput
{
  public function __construct($forceColors = false) {}

  public function diag() {}

  public function comment($message) {}

  public function info($message) {}

  public function error($message) {}

  public function echoln($message, $style = null, $colorize = true) {}

  public function greenBar($message) {}

  public function redBar($message) {}
}