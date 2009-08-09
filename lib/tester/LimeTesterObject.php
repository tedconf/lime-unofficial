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
  private static
    $equal    = array(),
    $unequal  = array();

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
      // properties are transformed to keys in the following way:

      // private   $property => "\0Classname\0property"
      // protected $property => "\0*\0property"
      // public    $property => "property"

      if ($key{0} == "\0")
      {
        if ($key{1} == '*')
        {
          $key = substr($key, 3);
        }
        else
        {
          $key = substr($key, strlen(get_class($object)) + 2);
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

  public function assertEquals(LimeTesterInterface $expected)
  {
    // don't compare twice to allow for cyclic dependencies
    if (in_array(array($this->value, $expected->value), self::$equal, true) || in_array(array($expected->value, $this->value), self::$equal, true))
    {
      return;
    }

    self::$equal[] = array($this->value, $expected->value);

    parent::assertEquals($expected);
  }

  public function assertNotEquals(LimeTesterInterface $expected)
  {
    // don't compare twice to allow for cyclic dependencies
    if (in_array(array($this->value, $expected->value), self::$unequal, true) || in_array(array($expected->value, $this->value), self::$unequal, true))
    {
      return;
    }

    self::$unequal[] = array($this->value, $expected->value);

    parent::assertNotEquals($expected);
  }

  public function assertSame(LimeTesterInterface $expected)
  {
    if ($this->object !== $expected->object)
    {
      throw new LimeAssertionFailedException($this, $expected);
    }

    parent::assertSame($expected);
  }

  public function assertNotSame(LimeTesterInterface $expected)
  {
    if ($this->object === $expected->object)
    {
      throw new LimeAssertionFailedException($this, $expected);
    }
  }
}