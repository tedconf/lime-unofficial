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


// @Test: assertGreaterThan() throws an exception if the given value is equal

  // fixtures
  $actual = new LimeTesterInteger(1);
  $expected = new LimeTesterInteger(1);
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertGreaterThan($expected);


// @Test: assertGreaterThan() throws an exception if the given value is greater

  // fixtures
  $actual = new LimeTesterInteger(1);
  $expected = new LimeTesterInteger(2);
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertGreaterThan($expected);


// @Test: assertGreaterThanOrEqual() throws an exception if the given value is greater

  // fixtures
  $actual = new LimeTesterInteger(1);
  $expected = new LimeTesterInteger(2);
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertGreaterThanOrEqual($expected);


// @Test: assertLessThanOrEqual() throws an exception if the given value is smaller

  // fixtures
  $actual = new LimeTesterInteger(2);
  $expected = new LimeTesterInteger(1);
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertLessThanOrEqual($expected);


// @Test: assertLessThan() throws an exception if the given value is equal

  // fixtures
  $actual = new LimeTesterInteger(1);
  $expected = new LimeTesterInteger(1);
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertLessThan($expected);


// @Test: assertLessThan() throws an exception if the given value is smaller

  // fixtures
  $actual = new LimeTesterInteger(2);
  $expected = new LimeTesterInteger(1);
  // test
  $t->expect('LimeNotEqualException');
  $actual->assertLessThan($expected);