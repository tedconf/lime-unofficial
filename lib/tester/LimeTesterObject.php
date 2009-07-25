<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeTesterObject extends LimeTesterArray
{
  private
    $object = null;

  public function __construct($object)
  {
    $this->object = $object;
    $this->type = get_class($object);

    $array = array();

    foreach ((array)$object as $key => $value)
    {
      // private and protected properties start with \0
      if ($key{0} == "\0")
      {
        // private properties start with the class
        if (strpos($key, $this->type) === 1)
        {
          $key = substr($key, strlen($this->type)+2);
        }
        // protected properties start with *
        else
        {
          $key = substr($key, 3);
        }
      }

      $array[$key] = $value;
    }

    parent::__construct($array);
  }

  protected function getType()
  {
    return 'object('.$this->type.')';
  }

  public function assertEquals($expected, $strict = false)
  {
    parent::assertEquals($expected, $strict);

    // still no exceptions, so properties are the same
    if ($strict && $this->object !== $expected->object)
    {
      throw new LimeNotEqualException($this, $expected);
    }
  }

  public function assertNotEquals($expected, $strict = false)
  {
    try
    {
      parent::assertNotEquals($expected, $strict);
    }
    catch (LimeNotEqualException $e)
    {
      if (!$strict || $this->object === $expected->object)
      {
        throw $e;
      }
    }
  }
}