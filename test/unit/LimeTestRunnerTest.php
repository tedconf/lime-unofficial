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

  public function __construct(LimeTest $test)
  {
    $this->methodCalls = new LimeExpectationList($test);
  }

  public function __call($method, $args)
  {
    $this->methodCalls->addActual($method);
  }

  public function handleErrorSuccessful(Exception $error)
  {
    $this->methodCalls->addActual('handleErrorSuccessful');

    return true;
  }

  public function handleErrorFailed(Exception $error)
  {
    $this->methodCalls->addActual('handleErrorFailed');

    return false;
  }

  public function testThrowsError()
  {
    1/0;
  }

  public function testThrowsException()
  {
    throw new Exception();
  }
}


$t = new LimeTest(8);


$t->diag('The before callbacks are called before each test method');

  // fixtures
  $test = new TestCase($t);
  $r = new LimeTestRunner();
  $r->addBefore(array($test, 'setUp'));
  $r->addTest(array($test, 'testDoSomething'));
  $r->addTest(array($test, 'testDoSomethingElse'));
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
  $test = new TestCase($t);
  $r = new LimeTestRunner();
  $r->addAfter(array($test, 'tearDown'));
  $r->addTest(array($test, 'testDoSomething'));
  $r->addTest(array($test, 'testDoSomethingElse'));
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
  $test = new TestCase($t);
  $r = new LimeTestRunner();
  $r->addBeforeAll(array($test, 'setUp'));
  $r->addTest(array($test, 'testDoSomething'));
  $r->addTest(array($test, 'testDoSomethingElse'));
  $test->methodCalls->addExpected('setUp');
  $test->methodCalls->addExpected('testDoSomething');
  $test->methodCalls->addExpected('testDoSomethingElse');
  // test
  $r->run();
  // assertions
  $test->methodCalls->verify();


$t->diag('The after-all callbacks are called before the whole test suite');

  // fixtures
  $test = new TestCase($t);
  $r = new LimeTestRunner();
  $r->addAfterAll(array($test, 'tearDown'));
  $r->addTest(array($test, 'testDoSomething'));
  $r->addTest(array($test, 'testDoSomethingElse'));
  $test->methodCalls->addExpected('testDoSomething');
  $test->methodCalls->addExpected('testDoSomethingElse');
  $test->methodCalls->addExpected('tearDown');
  // test
  $r->run();
  // assertions
  $test->methodCalls->verify();


$t->diag('The error handlers are called when a test throws an error');

  // fixtures
  $test = new TestCase($t);
  $r = new LimeTestRunner();
  $r->addTest(array($test, 'testThrowsError'));
  $r->addErrorHandler(array($test, 'handleErrorFailed'));
  $r->addErrorHandler(array($test, 'handleErrorSuccessful'));
  $test->methodCalls->addExpected('handleErrorFailed');
  $test->methodCalls->addExpected('handleErrorSuccessful');
  // test
  $r->run();
  // assertions
  $test->methodCalls->verify();


$t->diag('If no error handler returns true, the error is thrown as LimeError exception');

  // fixtures
  $test = new TestCase($t);
  $r = new LimeTestRunner();
  $r->addTest(array($test, 'testThrowsError'));
  $r->addExceptionHandler(array($test, 'handleErrorFailed'));
  // test
  try
  {
    $r->run();
    $t->fail('A "LimeError" was thrown');
  }
  catch (LimeError $e)
  {
    $t->pass('A "LimeError" was thrown');
  }



$t->diag('The exception handlers are called when a test throws an exception');

  // fixtures
  $test = new TestCase($t);
  $r = new LimeTestRunner();
  $r->addTest(array($test, 'testThrowsException'));
  $r->addExceptionHandler(array($test, 'handleErrorFailed'));
  $r->addExceptionHandler(array($test, 'handleErrorSuccessful'));
  $test->methodCalls->addExpected('handleErrorFailed');
  $test->methodCalls->addExpected('handleErrorSuccessful');
  // test
  $r->run();
  // assertions
  $test->methodCalls->verify();


$t->diag('If no exception handler returns true, the exception is thrown again');

  // fixtures
  $test = new TestCase($t);
  $r = new LimeTestRunner();
  $r->addTest(array($test, 'testThrowsException'));
  $r->addExceptionHandler(array($test, 'handleErrorFailed'));
  // test
  try
  {
    $r->run();
    $t->fail('The exception was thrown');
  }
  catch (Exception $e)
  {
    $t->pass('The exception was thrown');
  }
