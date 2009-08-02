<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

abstract class LimeTester implements LimeTesterInterface
{
  protected static
    $testers = array(
      'null'      => 'LimeTesterScalar',
      'integer'   => 'LimeTesterInteger',
      'boolean'   => 'LimeTesterScalar',
      'string'    => 'LimeTesterString',
      'double'    => 'LimeTesterDouble',
      'array'     => 'LimeTesterArray',
      'object'    => 'LimeTesterObject',
      'resource'  => 'LimeTesterResource',
    );

  protected
    $value = null,
    $type  = null;

  public static function create($value)
  {
    $type = null;

    if (is_null($value))
    {
      $type = 'null';
    }
    else if (is_object($value) && array_key_exists(get_class($value), self::$testers))
    {
      $type = get_class($value);
    }
    else if (is_object($value))
    {
      $class = new ReflectionClass($value);

      foreach ($class->getInterfaces() as $interface)
      {
        if (array_key_exists($interface->getName(), self::$testers))
        {
          $type = $interface->getName();
          break;
        }
      }

      $parentClass = $class;

      while ($parentClass = $parentClass->getParentClass())
      {
        if (array_key_exists($parentClass->getName(), self::$testers))
        {
          $type = $parentClass->getName();
          break;
        }
      }

      if (!empty($type))
      {
        // cache the tester
        self::$testers[$class->getName()] = self::$testers[$type];
      }
    }

    if (empty($type))
    {
      if (array_key_exists(gettype($value), self::$testers))
      {
        $type = gettype($value);
      }
      else
      {
        throw new InvalidArgumentException(sprintf('No tester is registered for type "%s"', gettype($value)));
      }
    }

    $class = self::$testers[$type];

    return new $class($value);
  }

  public static function register($type, $tester)
  {
    if (!class_exists($tester))
    {
      throw new InvalidArgumentException(sprintf('The class "%s" does not exist', $tester));
    }

    $class = new ReflectionClass($tester);

    if (!$class->implementsInterface('LimeTesterInterface'))
    {
      throw new InvalidArgumentException('Testers must implement "LimeTesterInterface"');
    }

    self::$testers[$type] = $tester;
  }

  public function __construct($value)
  {
    $this->value = $value;
  }

  private function notImplemented($method)
  {
    throw new BadMethodCallException(sprintf('"%s" is not implemtented for type "%s"', $method, $this->type));
  }

  public function assertEquals(LimeTesterInterface $expected)
  {
    $this->notImplemented('==');
  }

  public function assertNotEquals(LimeTesterInterface $expected)
  {
    $this->notImplemented('!=');
  }

  public function assertSame(LimeTesterInterface $expected)
  {
    $this->notImplemented('===');
  }

  public function assertNotSame(LimeTesterInterface $expected)
  {
    $this->notImplemented('!==');
  }

  public function assertLike(LimeTesterInterface $expected)
  {
    $this->notImplemented('like');
  }

  public function assertUnlike(LimeTesterInterface $expected)
  {
    $this->notImplemented('unlike');
  }

  public function assertGreaterThan(LimeTesterInterface $expected)
  {
    $this->notImplemented('>');
  }

  public function assertGreaterThanOrEqual(LimeTesterInterface $expected)
  {
    $this->notImplemented('>=');
  }

  public function assertLessThan(LimeTesterInterface $expected)
  {
    $this->notImplemented('<');
  }

  public function assertLessThanOrEqual(LimeTesterInterface $expected)
  {
    $this->notImplemented('<=');
  }
}