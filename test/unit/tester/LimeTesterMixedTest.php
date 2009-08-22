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

class TestClassWithToString
{
  public function __toString()
  {
    return 'foobar';
  }
}

$t = new LimeTest(12);


// @Test: assertEquals() throws an exception when comparing objects with scalars (1)

  // fixtures
  $actual = new LimeTesterObject(new stdClass());
  $expected = new LimeTesterScalar(false);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception when comparing objects with scalars (2)

  // fixtures
  $actual = new LimeTesterScalar(false);
  $expected = new LimeTesterObject(new stdClass());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws no exception when comparing strings with objects featuring __toString() (1)

  // fixtures
  $actual = new LimeTesterString('foobar');
  $expected = new LimeTesterObject(new TestClassWithToString());
  // test
  $actual->assertEquals($expected);


// @Test: assertEquals() throws no exception when comparing strings with objects featuring __toString() (2)

  // fixtures
  $actual = new LimeTesterObject(new TestClassWithToString());
  $expected = new LimeTesterString('foobar');
  // test
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception when comparing arrays with scalars (1)

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterScalar(false);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception when comparing arrays with scalars (2)

  // fixtures
  $actual = new LimeTesterScalar(false);
  $expected = new LimeTesterArray(array());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception when comparing arrays with objects (1)

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterObject(new stdClass());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception when comparing arrays with objects (2)

  // fixtures
  $actual = new LimeTesterObject(new stdClass());
  $expected = new LimeTesterArray(array());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertNotEquals() throws no exception when comparing objects with scalars (1)

  // fixtures
  $actual = new LimeTesterObject(new stdClass());
  $expected = new LimeTesterScalar(false);
  // test
  $actual->assertNotEquals($expected);


// @Test: assertNotEquals() throws no exception when comparing objects with scalars (2)

  // fixtures
  $expected = new LimeTesterObject(new stdClass());
  $actual = new LimeTesterScalar(false);
  // test
  $actual->assertNotEquals($expected);


// @Test: assertNotEquals() throws no exception when comparing arrays with scalars (1)

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterScalar(false);
  // test
  $actual->assertNotEquals($expected);


// @Test: assertNotEquals() throws no exception when comparing arrays with scalars (2)

  // fixtures
  $actual = new LimeTesterScalar(false);
  $expected = new LimeTesterArray(array());
  // test
  $actual->assertNotEquals($expected);


// @Test: assertNotEquals() throws no exception when comparing arrays with objects (1)

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterObject(new stdClass());
  // test
  $actual->assertNotEquals($expected);


// @Test: assertNotEquals() throws no exception when comparing arrays with objects (2)

  // fixtures
  $actual = new LimeTesterObject(new stdClass());
  $expected = new LimeTesterArray(array());
  // test
  $actual->assertNotEquals($expected);


// @Test: assertSame() throws an exception when comparing arrays with scalars (1)

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterScalar(false);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertSame() throws an exception when comparing arrays with scalars (2)

  // fixtures
  $actual = new LimeTesterScalar(false);
  $expected = new LimeTesterArray(array());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertSame() throws an exception when comparing arrays with objects (1)

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterObject(new stdClass());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertSame() throws an exception when comparing arrays with objects (2)

  // fixtures
  $actual = new LimeTesterObject(new stdClass());
  $expected = new LimeTesterArray(array());
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertSame() throws an exception when comparing doubles with strings (1)

  // fixtures
  $actual = new LimeTesterDouble(1.0);
  $expected = new LimeTesterString('1.0');
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);
  $actual->assertNotSame($expected);


// @Test: assertSame() throws an exception when comparing doubles with strings (2)

  // fixtures
  $actual = new LimeTesterString('1.0');
  $expected = new LimeTesterDouble(1.0);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertNotSame() throws no exception when comparing arrays with scalars (1)

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterScalar(false);
  // test
  $actual->assertNotSame($expected);


// @Test: assertNotSame() throws no exception when comparing arrays with scalars (2)

  // fixtures
  $actual = new LimeTesterScalar(false);
  $expected = new LimeTesterArray(array());
  // test
  $actual->assertNotSame($expected);


// @Test: assertNotSame() throws no exception when comparing arrays with objects (1)

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterObject(new stdClass());
  // test
  $actual->assertNotSame($expected);


// @Test: assertNotSame() throws no exception when comparing arrays with objects (2)

  // fixtures
  $actual = new LimeTesterObject(new stdClass());
  $expected = new LimeTesterArray(array());
  // test
  $actual->assertNotSame($expected);


// @Test: assertNotSame() throws no exception when comparing doubles with strings (1)

  // fixtures
  $actual = new LimeTesterDouble(1.0);
  $expected = new LimeTesterString('1.0');
  // test
  $actual->assertNotSame($expected);


// @Test: assertNotSame() throws no exception when comparing doubles with strings (2)

  // fixtures
  $actual = new LimeTesterString('1.0');
  $expected = new LimeTesterDouble(1.0);
  // test
  $actual->assertNotSame($expected);


