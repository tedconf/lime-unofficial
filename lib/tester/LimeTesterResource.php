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

  public function assertEquals(LimeTesterInterface $expected)
  {
    if ($this->value != $expected->value)
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }

  public function assertNotEquals(LimeTesterInterface $expected)
  {
    if ($this->value == $expected->value)
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }

  public function assertSame(LimeTesterInterface $expected)
  {
    $this->assertEquals($expected);
  }

  public function assertNotSame(LimeTesterInterface $expected)
  {
    $this->assertNotEquals($expected);
  }

  public function __toString()
  {
    return sprintf('resource(%s) of type (%s)', (integer)$this->value, get_resource_type($this->value));
  }
}