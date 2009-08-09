<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeTesterException extends LimeTesterObject
{
  public function __construct(Exception $exception)
  {
    parent::__construct($exception);

    unset($this->value['file']);
    unset($this->value['line']);
    unset($this->value['trace']);
  }
}