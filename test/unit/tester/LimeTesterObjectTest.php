<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

include dirname(__FILE__).'/../../bootstrap/unit.php';

class TestClass
{
  private $a;
  protected $b = 1;
  public $c = 2;

  public function __construct($a = 0)
  {
    $this->a = $a;
  }
}

LimeAnnotationSupport::enable();

$t = new LimeTest(5);


// @Test: assertEquals() throws an exception if the other tester is no LimeTesterObject

  // fixtures
  $actual = new LimeTesterObject(new TestClass());
  $expected = new LimeTesterScalar(false);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception if values don't match

  // fixtures
  $actual = new LimeTesterObject(new TestClass(0));
  $expected = new LimeTesterObject(new TestClass(1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws no exception if values match

  // fixtures
  $actual = new LimeTesterObject(new TestClass());
  $expected = new LimeTesterObject(new TestClass());
  // test
  $actual->assertEquals($expected);


// @Test: assertSame() throws an exception if objects are not the same

  // fixtures
  $actual = new LimeTesterObject(new TestClass());
  $expected = new LimeTesterObject(new TestClass());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertEquals() throws no exception if objects are the same and strict is set

  // fixtures
  $object = new TestClass();
  $actual = new LimeTesterObject($object);
  $expected = new LimeTesterObject($object);
  // test
  $actual->assertEquals($expected);


// @Test: assertNotEquals() throws no exception if the other tester is no LimeTesterObject

  // fixtures
  $actual = new LimeTesterObject(new TestClass());
  $expected = new LimeTesterScalar(false);
  // test
  $actual->assertNotEquals($expected);


// @Test: assertNotEquals() throws an exception if the objects are equal

  // fixtures
  $actual = new LimeTesterObject(new TestClass());
  $expected = new LimeTesterObject(new TestClass());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertNotEquals($expected);


// @Test: assertNotSame() throws an exception if the objects are identical

  // fixtures
  $object = new TestClass();
  $actual = new LimeTesterObject($object);
  $expected = new LimeTesterObject($object);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertNotSame($expected);


// @Test: assertNotSame() throws no exception if the objects are equal

  // fixtures
  $actual = new LimeTesterObject(new TestClass());
  $expected = new LimeTesterObject(new TestClass());
  // test
  $actual->assertNotSame($expected);

