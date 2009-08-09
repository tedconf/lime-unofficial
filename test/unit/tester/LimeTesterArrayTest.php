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

LimeAnnotationSupport::enable();

$t = new LimeTest(16);


// @Test: assertEquals() throws an exception if the other tester is no LimeTesterArray

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterScalar(false);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception if the other tester is a LimeTesterObject

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterObject(new stdClass());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception if keys are missing

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterArray(array(0 => 1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception if keys are unexpected

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception if values don't match

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array(0 => 2));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws no exception if the order is different

  // fixtures
  $actual = new LimeTesterArray(array('a' => 1, 'b' => 2));
  $expected = new LimeTesterArray(array('b' => 2, 'a' => 1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertEquals() throws no exception if values match

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array(0 => 1));
  // test
  $actual->assertEquals($expected);


// @Test: assertNotEquals() throws no exception if the other tester is no LimeTesterArray

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterScalar(false);
  // test
  $actual->assertNotEquals($expected);


// @Test: assertNotEquals() throws no exception if the other tester is a LimeTesterObject

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterObject(new stdClass());
  // test
  $actual->assertNotEquals($expected);


// @Test: assertNotEquals() throws an exception if the arrays are equal

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array(0 => 1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertNotEquals($expected);


// @Test: assertSame() throws an exception if the other tester is no LimeTesterArray

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterScalar(false);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertSame() throws an exception if the other tester is a LimeTesterObject

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterObject(new stdClass());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertSame() throws an exception if keys are missing

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterArray(array(0 => 1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertSame() throws an exception if keys are unexpected

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertSame() throws an exception if types are different

  // fixtures
  $actual = new LimeTesterArray(array(1));
  $expected = new LimeTesterArray(array('1'));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertSame() throws an exception if the order is different

  // fixtures
  $actual = new LimeTesterArray(array('a' => 1, 'b' => 2));
  $expected = new LimeTesterArray(array('b' => 2, 'a' => 1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertSame() throws no exception if values match

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array(0 => 1));
  // test
  $actual->assertSame($expected);


// @Test: assertNotSame() throws no exception if the other tester is no LimeTesterArray

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterScalar(false);
  // test
  $actual->assertNotSame($expected);


// @Test: assertNotSame() throws no exception if the other tester is a LimeTesterObject

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterObject(new stdClass());
  // test
  $actual->assertNotSame($expected);


// @Test: assertNotSame() throws an exception if the arrays are equal

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array(0 => 1));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertNotSame($expected);


// @Test: assertNotSame() throws no exception if the types differ

  // fixtures
  $actual = new LimeTesterArray(array(1));
  $expected = new LimeTesterArray(array('1'));
  // test
  $actual->assertNotSame($expected);


// @Test: assertNotSame() throws no exception if the order differs

  // fixtures
  $actual = new LimeTesterArray(array('a' => 1, 'b' => 2));
  $expected = new LimeTesterArray(array('b' => 2, 'a' => 1));
  // test
  $actual->assertNotSame($expected);


// @Test: assertContains() throws an exception if a value is not in the array

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = LimeTester::create(0);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertContains($expected);


// @Test: assertContains() throws no exception if a value is in the array

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = LimeTester::create(1);
  // test
  $actual->assertContains($expected);


// @Test: assertNotContains() throws an exception if a value is in the array

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = LimeTester::create(1);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertNotContains($expected);


// @Test: assertNotContains() throws no exception if a value is not in the array

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = LimeTester::create(0);
  // test
  $actual->assertNotContains($expected);

