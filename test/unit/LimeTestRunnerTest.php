<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

include dirname(__FILE__).'/../bootstrap/unit.php';


class TestCase
{
  public $methodCalls;

  public function __call($method, $args)
  {
    $this->methodCalls->addActual($method);
  }
}


$t = new LimeTest(4);


$t->diag('The before callbacks are called before each test method');

  // fixtures
  $test = new TestCase();
  $r = new LimeTestRunner();
  $r->addBefore(array($test, 'setUp'));
  $r->addTest(array($test, 'testDoSomething'));
  $r->addTest(array($test, 'testDoSomethingElse'));
  $test->methodCalls = new LimeExpectationList($t);
  $test->methodCalls->addExpected('setUp');
  $test->methodCalls->addExpected('testDoSomething');
  $test->methodCalls->addExpected('setUp');
  $test->methodCalls->addExpected('testDoSomethingElse');
  // test
  $r->run();
  // assertions
  $test->methodCalls->verify();


$t->diag('The after callbacks are called before each test method');

  // fixtures
  $test = new TestCase();
  $r = new LimeTestRunner();
  $r->addAfter(array($test, 'tearDown'));
  $r->addTest(array($test, 'testDoSomething'));
  $r->addTest(array($test, 'testDoSomethingElse'));
  $test->methodCalls = new LimeExpectationList($t);
  $test->methodCalls->addExpected('testDoSomething');
  $test->methodCalls->addExpected('tearDown');
  $test->methodCalls->addExpected('testDoSomethingElse');
  $test->methodCalls->addExpected('tearDown');
  // test
  $r->run();
  // assertions
  $test->methodCalls->verify();


$t->diag('The before-all callbacks are called before the whole test suite');

  // fixtures
  $test = new TestCase();
  $r = new LimeTestRunner();
  $r->addBeforeAll(array($test, 'setUp'));
  $r->addTest(array($test, 'testDoSomething'));
  $r->addTest(array($test, 'testDoSomethingElse'));
  $test->methodCalls = new LimeExpectationList($t);
  $test->methodCalls->addExpected('setUp');
  $test->methodCalls->addExpected('testDoSomething');
  $test->methodCalls->addExpected('testDoSomethingElse');
  // test
  $r->run();
  // assertions
  $test->methodCalls->verify();


$t->diag('The after-all callbacks are called before the whole test suite');

  // fixtures
  $test = new TestCase();
  $r = new LimeTestRunner();
  $r->addAfterAll(array($test, 'tearDown'));
  $r->addTest(array($test, 'testDoSomething'));
  $r->addTest(array($test, 'testDoSomethingElse'));
  $test->methodCalls = new LimeExpectationList($t);
  $test->methodCalls->addExpected('testDoSomething');
  $test->methodCalls->addExpected('testDoSomethingElse');
  $test->methodCalls->addExpected('tearDown');
  // test
  $r->run();
  // assertions
  $test->methodCalls->verify();
