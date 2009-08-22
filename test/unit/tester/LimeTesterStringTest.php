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

$t = new LimeTest(3);


// @Test: __toString() returns the string in quotes

  $actual = new LimeTesterString('a\d');
  $t->is($actual->__toString(), "'a\d'", 'The string in quotes is returned');


// @Test: assertLike() throws an exception if the regular expression does not match

  // fixtures
  $actual = new LimeTesterString('a');
  $expected = new LimeTesterString('/\d/');
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertLike($expected);


// @Test: assertLike() throws no exception if the regular expression does match

  // fixtures
  $actual = new LimeTesterString('1');
  $expected = new LimeTesterString('/\d/');
  // test
  $actual->assertLike($expected);


// @Test: assertUnlike() throws an exception if the regular expression does match

  // fixtures
  $actual = new LimeTesterString('1');
  $expected = new LimeTesterString('/\d/');
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertUnlike($expected);


// @Test: assertUnlike() throws no exception if the regular expression does not match

  // fixtures
  $actual = new LimeTesterString('a');
  $expected = new LimeTesterString('/\d/');
  // test
  $actual->assertUnlike($expected);


