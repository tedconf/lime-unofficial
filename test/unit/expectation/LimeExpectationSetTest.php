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
require_once dirname(__FILE__).'/../MockLimeAssert.php';


$t = new LimeTest(7);


$t->diag('Expected values can be added in any order');

  // fixtures
  $mock = new MockLimeAssert();
  $s = new LimeExpectationSet($mock);
  // test
  $s->addExpected(1);
  $s->addExpected(3);
  $s->addExpected(2);
  $s->addActual(2);
  $s->addActual(3);
  $s->addActual(1);
  $s->verify();
  // assertions
  $t->is($mock->passes, 1, 'One test passed');
  $t->is($mock->fails, 0, 'No test failed');


$t->diag('Expected values can be added any number of times');

  // fixtures
  $mock = new MockLimeAssert();
  $s = new LimeExpectationSet($mock);
  // test
  $s->addExpected(1);
  $s->addActual(1);
  $s->addActual(1);
  $s->verify();
  // assertions
  $t->is($mock->passes, 1, 'One test passed');
  $t->is($mock->fails, 0, 'No test failed');


$t->diag('Exceptions are thrown if unexpected values are added');

  // fixtures
  $mock = new MockLimeAssert();
  $s = new LimeExpectationSet($mock);
  $s->addExpected(1);
  // test
  try
  {
    $s->addActual(2);
    $t->fail('A "LimeAssertionException" is thrown');
  }
  catch (LimeAssertionException $e)
  {
    $t->pass('A "LimeAssertionException" is thrown');
  }


$t->diag('setFailOnVerify() suppresses exceptions');

  // fixtures
  $mock = new MockLimeAssert();
  $s = new LimeExpectationSet($mock);
  // test
  $s->setFailOnVerify();
  $s->addExpected(1);
  $s->addActual(2);
  $s->verify();
  // assertions
  $t->is($mock->passes, 0, 'No test passed');
  $t->is($mock->fails, 1, 'One test failed');


