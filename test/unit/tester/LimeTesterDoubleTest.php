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

$t = new LimeTest(3);


// @Test: __toString() returns the value as float

  $actual = new LimeTesterDouble(1);
  $t->ok($actual->__toString() === '1.0', 'The value is correct');


// @Test: assertEquals() throws no exception if the difference between the doubles is very small

  // fixtures
  $actual = new LimeTesterDouble(1/3);
  $expected = new LimeTesterDouble(1 - 2/3);
  // test
  $actual->assertEquals($expected);


// @Test: assertSame() throws an exception if the types of the values differ

  // fixtures
  $actual = new LimeTesterDouble(1.0);
  $expected = new LimeTesterString('1.0');
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertSame($expected);


// @Test: assertNotEquals() throws an exception if the difference between the doubles is very small

  // fixtures
  $actual = new LimeTesterDouble(1/3);
  $expected = new LimeTesterDouble(1 - 2/3);
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertNotEquals($expected);


// @Test: assertNotSame() throws no exception if the types of the values differ

  // fixtures
  $actual = new LimeTesterDouble(1.0);
  $expected = new LimeTesterString('1.0');
  // test
  $actual->assertNotSame($expected);

