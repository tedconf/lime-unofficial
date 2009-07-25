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


// @Test: assertEquals() throws an exception if the values differ

  // fixtures
  $actual = new LimeTesterScalar('a');
  $expected = new LimeTesterScalar('b');
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws no exception if the values are equal, but different types

  // fixtures
  $actual = new LimeTesterScalar('0');
  $expected = new LimeTesterScalar(0);
  // test
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception if the values have different types and strict is set

  // fixtures
  $actual = new LimeTesterScalar('0');
  $expected = new LimeTesterScalar(0);
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertEquals($expected, true);


// @Test: assertNotEquals() throws an exception if values are equal

  // fixtures
  $actual = new LimeTesterScalar(1);
  $expected = new LimeTesterScalar(1);
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertNotEquals($expected);


// @Test: assertNotEquals() throws no exception if values are equal but types are different and strict is set

  // fixtures
  $actual = new LimeTesterScalar('1');
  $expected = new LimeTesterScalar(1);
  // test
  $actual->assertNotEquals($expected, true);