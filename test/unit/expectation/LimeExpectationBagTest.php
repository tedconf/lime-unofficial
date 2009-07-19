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


// @Before

  $mock = LimeMock::create('LimeOutputInterface', $t);
  $b = new LimeExpectationBag($mock);


// @After

  $mock = null;
  $b = null;


// @Test: Expected values can be added in any order

  // fixtures
  $mock->invoke('pass')->once()->anyParameters();
  $mock->invoke('fail')->never();
  $mock->replay();
  // test
  $b->addExpected(1);
  $b->addExpected(3);
  $b->addExpected(2);
  $b->addActual(2);
  $b->addActual(3);
  $b->addActual(1);
  $b->verify();
  // assertions
  $mock->verify();


// @Test: Exceptions are thrown if unexpected values are added

  // test
  $b->addExpected(1);
  $t->expect('LimeAssertionException');
  $b->addActual(2);


// @Test: Exceptions are thrown if expected values are added too often

  // test
  $b->addExpected(1);
  $b->addActual(1);
  $t->expect('LimeAssertionException');
  $b->addActual(1);


// @Test: setFailOnVerify() suppresses exceptions

  // fixtures
  $mock->invoke('pass')->never();
  $mock->invoke('fail')->once()->anyParameters();
  $mock->replay();
  // test
  $b->setFailOnVerify();
  $b->addExpected(1);
  $b->addActual(1);
  $b->addActual(1);
  $b->verify();
  // assertions
  $mock->verify();

