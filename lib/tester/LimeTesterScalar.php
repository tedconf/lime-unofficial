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

  private function equals(LimeTesterInterface $other)
  {
    $exp1 = $this->value;
    $exp2 = $other->value;

    // always compare as strings to avoid strange behaviour
    // otherwise 0 == 'Foobar'
    if (is_string($exp1) || is_string($exp2))
    {
      $exp1 = (string)$exp1;
      $exp2 = (string)$exp2;
    }

    return $exp1 == $exp2;
  }

  public function assertEquals(LimeTesterInterface $expected)
  {
    if (!$this->equals($expected))
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }

  public function assertSame(LimeTesterInterface $expected)
  {
    if ($this->value !== $expected->value)
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }

  public function assertNotEquals(LimeTesterInterface $expected)
  {
    if ($this->equals($expected))
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }

  public function assertNotSame(LimeTesterInterface $expected)
  {
    if ($this->value === $expected->value)
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }

  public function assertGreaterThan(LimeTesterInterface $expected)
  {
    if ($this->value <= $expected->value)
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }

  public function assertGreaterThanOrEqual(LimeTesterInterface $expected)
  {
    if ($this->value < $expected->value)
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }

  public function assertLessThanOrEqual(LimeTesterInterface $expected)
  {
    if ($this->value > $expected->value)
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }

  public function assertLessThan(LimeTesterInterface $expected)
  {
    if ($this->value >= $expected->value)
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }
}