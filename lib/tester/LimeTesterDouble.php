<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeTesterDouble extends LimeTesterInteger
{
  const
    EPSILON = 0.0000000001;

  protected
    $type = 'double';

  public function __construct($value)
  {
    parent::__construct((double)$value);
  }

  public function __toString()
  {
    if ($this->value == round($this->value))
    {
      return sprintf('%.1f', $this->value);
    }
    else
    {
      return (string)$this->value;
    }
  }

  public function assertEquals(LimeTesterInterface $expected)
  {
    if (abs($this->value - $expected->value) >= self::EPSILON)
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }

  public function assertNotEquals(LimeTesterInterface $expected)
  {
    if (abs($this->value - $expected->value) < self::EPSILON)
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }

  public function assertSame(LimeTesterInterface $expected)
  {
    $this->assertEquals($expected);

    if (gettype($this->value) != gettype($expected->value))
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }

  public function assertNotSame(LimeTesterInterface $expected)
  {
    try
    {
      $this->assertEquals($expected);
    }
    catch (LimeAssertionFailedException $e)
    {
      if (gettype($this->value) == gettype($expected->value))
      {
        throw $e;
      }
    }
  }
}