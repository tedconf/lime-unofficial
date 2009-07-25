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

$t = new LimeTest(6);


// @Test: assertEquals() throws an exception if the other tester is no LimeTesterArray

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterScalar(false);
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception if the other tester is a LimeTesterObject

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterObject(new stdClass());
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception if keys are missing

  // fixtures
  $actual = new LimeTesterArray(array());
  $expected = new LimeTesterArray(array(0 => 1));
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception if keys are unexpected

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array());
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws an exception if values don't match

  // fixtures
  $actual = new LimeTesterArray(array(0 => 1));
  $expected = new LimeTesterArray(array(0 => 2));
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertEquals($expected);


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
  $t->expect('LimeNotEqualException');
  $actual->assertNotEquals($expected);

