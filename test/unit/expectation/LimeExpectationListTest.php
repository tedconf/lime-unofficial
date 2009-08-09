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

$t = new LimeTest(4);


// @Before

  $output = LimeMock::create('LimeOutputInterface', $t);
  $l = new LimeExpectationList($output);


// @After

  $output = null;
  $l = null;


// @Test: Exceptions are thrown if unexpected values are added

  // test
  $l->addExpected(1);
  $l->addExpected(2);
  $t->expect('LimeAssertionException');
  $l->addActual(2);


// @Test: Exceptions are thrown if expected values are added too often

  // test
  $l->addExpected(1);
  $l->addActual(1);
  $t->expect('LimeAssertionException');
  $l->addActual(1);


// @Test: setFailOnVerify() suppresses exceptions

  // fixtures
  $output->any('pass')->never();
  $output->any('fail')->once();
  $output->replay();
  // test
  $l->setFailOnVerify();
  $l->addExpected(1);
  $l->addActual(1);
  $l->addActual(1);
  $l->verify();
  // assertions
  $output->verify();
