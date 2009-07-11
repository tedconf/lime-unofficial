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


$t = new LimeAssert(4);


$t->diag('Exceptions are thrown if unexpected values are added');

  // fixtures
  $mock = new MockLimeAssert();
  $l = new LimeExpectationList($mock);
  $l->addExpected(1);
  $l->addExpected(2);
  // test
  try
  {
    $l->addActual(2);
    $t->fail('A "LimeAssertionException" is thrown');
  }
  catch (LimeAssertionException $e)
  {
    $t->pass('A "LimeAssertionException" is thrown');
  }


$t->diag('Exceptions are thrown if expected values are added too often');

  // fixtures
  $mock = new MockLimeAssert();
  $l = new LimeExpectationList($mock);
  $l->addExpected(1);
  $l->addActual(1);
  // test
  try
  {
    $l->addActual(1);
    $t->fail('A "LimeAssertionException" is thrown');
  }
  catch (LimeAssertionException $e)
  {
    $t->pass('A "LimeAssertionException" is thrown');
  }


$t->diag('setFailOnVerify() suppresses exceptions');

  // fixtures
  $mock = new MockLimeAssert();
  $l = new LimeExpectationList($mock);
  // test
  $l->setFailOnVerify();
  $l->addExpected(1);
  $l->addActual(1);
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($mock->passes, 0, 'No test passed');
  $t->is($mock->fails, 1, 'One test failed');
