<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeTesterString extends LimeTesterScalar
{
  protected
    $type = 'string';

  public function __toString()
  {
    return "'".$this->value."'";
  }

  public function assertLike(LimeTesterInterface $expected)
  {
    if (!preg_match($expected->value, $this->value))
    {
      throw new LimeTesterException($this, $expected);
    }
  }

  public function assertUnlike(LimeTesterInterface $expected)
  {
    if (preg_match($expected->value, $this->value))
    {
      throw new LimeTesterException($this, $expected);
    }
  }
}