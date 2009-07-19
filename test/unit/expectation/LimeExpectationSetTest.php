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

$t = new LimeTest(7);


// @Before

  $output = LimeMock::create('LimeOutputInterface', $t);
  $s = new LimeExpectationSet($output);


// @After

  $output = null;
  $s = null;


// @Test: Expected values can be added in any order

  // fixtures
  $output->invoke('pass')->once()->anyParameters();
  $output->invoke('fail')->never();
  $output->replay();
  // test
  $s->addExpected(1);
  $s->addExpected(3);
  $s->addExpected(2);
  $s->addActual(2);
  $s->addActual(3);
  $s->addActual(1);
  $s->verify();
  // assertions
  $output->verify();


// @Test: Expected values can be added any number of times

  // fixtures
  $output->invoke('pass')->once()->anyParameters();
  $output->invoke('fail')->never();
  $output->replay();
  // test
  $s->addExpected(1);
  $s->addActual(1);
  $s->addActual(1);
  $s->verify();
  // assertions
  $output->verify();



// @Test: Exceptions are thrown if unexpected values are added

  // test
  $s->addExpected(1);
  $t->expect('LimeAssertionException');
  $s->addActual(2);


// @Test: setFailOnVerify() suppresses exceptions

  // fixtures
  $output->invoke('pass')->never();
  $output->invoke('fail')->once()->anyParameters();
  $output->replay();
  // test
  $s->setFailOnVerify();
  $s->addExpected(1);
  $s->addActual(2);
  $s->verify();
  // assertions
  $output->verify();


