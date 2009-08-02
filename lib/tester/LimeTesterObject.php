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

  public static function toArray($object)
  {
    if (!is_object($object))
    {
      throw new InvalidArgumentException('The argument must be an object');
    }

    $array = array();

    foreach ((array)$object as $key => $value)
    {
      // private and protected properties start with \0
      if ($key{0} == "\0")
      {
        // private properties start with the class
        if (strpos($key, get_class($object)) === 1)
        {
          $key = substr($key, strlen(get_class($object))+2);
        }
        // protected properties start with *
        else
        {
          $key = substr($key, 3);
        }
      }

      $array[$key] = $value;
    }

    return $array;
  }

  public function __construct($object)
  {
    $this->object = $object;
    $this->type = get_class($object);

    parent::__construct(self::toArray($object));
  }

  protected function getType()
  {
    return 'object('.$this->type.')';
  }

  public function assertSame($expected)
  {
    $this->assertEquals($expected);

    // still no exceptions, so properties are the same
    if ($this->object !== $expected->object)
    {
      throw new LimeNotEqualException($this, $expected);
    }
  }

  public function assertNotSame($expected)
  {
    try
    {
      $this->assertNotEquals($expected);
    }
    catch (LimeNotEqualException $e)
    {
      if ($this->object === $expected->object)
      {
        throw $e;
      }
    }
  }
}