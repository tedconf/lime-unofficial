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
    $factory = null;

  protected
    $value = null,
    $type  = null;

  public static function create($value)
  {
    return self::getFactory()->create($value);
  }

  public static function register($type, $tester)
  {
    return self::getFactory()->register($type, $tester);
  }

  public static function unregister($type)
  {
    return self::getFactory()->unregister($type);
  }

  private static function getFactory()
  {
    if (is_null(self::$factory))
    {
      self::$factory = new LimeTesterFactory();
    }

    return self::$factory;
  }

  public function __construct($value)
  {
    $this->value = $value;
  }

  public function assertEquals(LimeTesterInterface $expected)
  {
    throw new LimeAssertionFailedException($this, $expected);
  }

  public function assertNotEquals(LimeTesterInterface $expected)
  {
    throw new LimeAssertionFailedException($this, $expected);
  }

  public function assertSame(LimeTesterInterface $expected)
  {
    throw new LimeAssertionFailedException($this, $expected);
  }

  public function assertNotSame(LimeTesterInterface $expected)
  {
    throw new LimeAssertionFailedException($this, $expected);
  }

  public function assertLike(LimeTesterInterface $expected)
  {
    throw new LimeAssertionFailedException($this, $expected);
  }

  public function assertUnlike(LimeTesterInterface $expected)
  {
    throw new LimeAssertionFailedException($this, $expected);
  }

  public function assertGreaterThan(LimeTesterInterface $expected)
  {
    throw new LimeAssertionFailedException($this, $expected);
  }

  public function assertGreaterThanOrEqual(LimeTesterInterface $expected)
  {
    throw new LimeAssertionFailedException($this, $expected);
  }

  public function assertLessThan(LimeTesterInterface $expected)
  {
    throw new LimeAssertionFailedException($this, $expected);
  }

  public function assertLessThanOrEqual(LimeTesterInterface $expected)
  {
    throw new LimeAssertionFailedException($this, $expected);
  }

  public function assertContains(LimeTesterInterface $expected)
  {
    throw new LimeAssertionFailedException($this, $expected);
  }

  public function assertNotContains(LimeTesterInterface $expected)
  {
    throw new LimeAssertionFailedException($this, $expected);
  }
}