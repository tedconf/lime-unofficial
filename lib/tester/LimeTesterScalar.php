<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeTesterScalar extends LimeTester
{
  protected
    $type = 'scalar';

  public function __construct($value)
  {
    $this->type = gettype($value);

    parent::__construct($value);
  }

  public function __toString()
  {
    return var_export($this->value, true);
  }

  public function assertEquals($expected)
  {
    if ($this->value != $expected->value)
    {
      throw new LimeNotEqualException($this, $expected);
    }
  }

  public function assertSame($expected)
  {
    if ($this->value !== $expected->value)
    {
      throw new LimeNotEqualException($this, $expected);
    }
  }

  public function assertNotEquals($expected)
  {
    if ($this->value == $expected->value)
    {
      throw new LimeNotEqualException($this, $expected);
    }
  }

  public function assertNotSame($expected)
  {
    if ($this->value === $expected->value)
    {
      throw new LimeNotEqualException($this, $expected);
    }
  }

  public function assertGreaterThan($expected)
  {
    if ($this->value <= $expected->value)
    {
      throw new LimeNotEqualException($this, $expected);
    }
  }

  public function assertGreaterThanOrEqual($expected)
  {
    if ($this->value < $expected->value)
    {
      throw new LimeNotEqualException($this, $expected);
    }
  }

  public function assertLessThanOrEqual($expected)
  {
    if ($this->value > $expected->value)
    {
      throw new LimeNotEqualException($this, $expected);
    }
  }

  public function assertLessThan($expected)
  {
    if ($this->value >= $expected->value)
    {
      throw new LimeNotEqualException($this, $expected);
    }
  }
}