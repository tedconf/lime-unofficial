<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeTesterResource extends LimeTester
{
  protected
    $type = 'resource';

  public function assertEquals($expected, $strict = false)
  {
  }

  public function __toString()
  {
  }
}