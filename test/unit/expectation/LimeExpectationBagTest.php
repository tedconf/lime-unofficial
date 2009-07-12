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

LimeAnnotationSupport::enable();

LimeAnnotationSupport::enable();
require_once dirname(__FILE__).'/../../MockLimeTest.php';


$t = new LimeTest(6);


$t->diag('Expected values can be added in any order');

  // fixtures
  $mock = new MockLimeTest();
  $b = new LimeExpectationBag($mock);
  // test
  $b->addExpected(1);
  $b->addExpected(3);
  $b->addExpected(2);
  $b->addActual(2);
  $b->addActual(3);
  $b->addActual(1);
  $b->verify();
  // assertions
  $t->is($mock->passes, 1, 'One test passed');
  $t->is($mock->fails, 0, 'No test failed');


$t->diag('Exceptions are thrown if unexpected values are added');

  // fixtures
  $mock = new MockLimeTest();
  $b = new LimeExpectationBag($mock);
  $b->addExpected(1);
  // test
  try
  {
    $b->addActual(2);
    $t->fail('A "LimeAssertionException" is thrown');
  }
  catch (LimeAssertionException $e)
  {
    $t->pass('A "LimeAssertionException" is thrown');
  }


$t->diag('Exceptions are thrown if expected values are added too often');

  // fixtures
  $mock = new MockLimeTest();
  $b = new LimeExpectationBag($mock);
  $b->addExpected(1);
  $b->addActual(1);
  // test
  try
  {
    $b->addActual(1);
    $t->fail('A "LimeAssertionException" is thrown');
  }
  catch (LimeAssertionException $e)
  {
    $t->pass('A "LimeAssertionException" is thrown');
  }


$t->diag('setFailOnVerify() suppresses exceptions');

  // fixtures
  $mock = new MockLimeTest();
  $b = new LimeExpectationBag($mock);
  // test
  $b->setFailOnVerify();
  $b->addExpected(1);
  $b->addActual(1);
  $b->addActual(1);
  $b->verify();
  // assertions
  $t->is($mock->passes, 0, 'No test passed');
  $t->is($mock->fails, 1, 'One test failed');

