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
require_once dirname(__FILE__).'/../../MockLimeTest.php';

LimeAnnotationSupport::enable();


$t = new LimeTest(11);


// @Before

  $mockTest = new MockLimeTest();
  $m = LimeMock::createStrict('TestClass', $mockTest);


// @After

  $mockTest = null;
  $m = null;


// @Test: ->verify() passes if methods were called in the correct order

  // test
  $m->testMethod1();
  $m->testMethod2('Foobar');
  $m->replay();
  $m->testMethod1();
  $m->testMethod2('Foobar');
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 2, 'Two tests passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: An exception is thrown if methods are called in the wrong order

  // fixtures
  $m->method1();
  $m->method2();
  $m->replay();
  $t->expect('LimeAssertionException');
  // test
  $m->method2();


// @Test: If ->setFailOnVerify() was called, ->verify() fails if methods were called in the wrong order

  // test
  $m->setFailOnVerify();
  $m->method1();
  $m->method2();
  $m->method3();
  $m->replay();
  $m->method3();
  $m->method1();
  $m->method2();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 2, 'Two tests passed');
  $t->is($mockTest->fails, 1, 'One test failed');


// @Test: The order of the tests remains intact when using times()

  // @Test: Case 1 - Assertion fails

  // fixtures
  $m->method1()->times(3);
  $m->method2();
  $m->replay();
  $t->expect('LimeAssertionException');
  // test
  $m->method1();
  $m->method1();
  $m->method2();

  // @Test: Case 2 - Assertion succeeds

  // fixtures
  $m->method1()->times(3);
  $m->method2();
  $m->replay();
  // test
  $m->method1();
  $m->method1();
  $m->method1();
  $m->method2();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 2, 'Two tests passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: The order of the tests remains intact when using atLeastOnce()

  // @Test: Case 1 - Assertion fails

  // fixtures
  $m->method1()->atLeastOnce();
  $m->method2();
  $m->replay();
  $t->expect('LimeAssertionException');
  // test
  $m->method2();

  // @Test: Case 2 - Assertion succeeds

  // fixtures
  $m->method1()->atLeastOnce();
  $m->method2();
  $m->replay();
  // test
  $m->method1();
  $m->method1();
  $m->method1();
  $m->method2();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 2, 'Two tests passed');
  $t->is($mockTest->fails, 0, 'No test failed');


