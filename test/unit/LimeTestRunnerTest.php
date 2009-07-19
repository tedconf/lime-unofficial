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


$t = new LimeTest(20);


$t->diag('The before callbacks are called before each test method');

  // fixtures
  $mock = LimeMock::createStrict('Mock', $t);
  $r = new LimeTestRunner();
  $r->addBefore(array($mock, 'setUp'));
  $r->addTest(array($mock, 'testDoSomething'));
  $r->addTest(array($mock, 'testDoSomethingElse'));
  $mock->setUp();
  $mock->testDoSomething();
  $mock->setUp();
  $mock->testDoSomethingElse();
  $mock->replay();
  // test
  $r->run();
  // assertions
  $mock->verify();


$t->diag('The after callbacks are called before each test method');

  // fixtures
  $mock = LimeMock::createStrict('Mock', $t);
  $r = new LimeTestRunner();
  $r->addAfter(array($mock, 'tearDown'));
  $r->addTest(array($mock, 'testDoSomething'));
  $r->addTest(array($mock, 'testDoSomethingElse'));
  $mock->testDoSomething();
  $mock->tearDown();
  $mock->testDoSomethingElse();
  $mock->tearDown();
  $mock->replay();
  // test
  $r->run();
  // assertions
  $mock->verify();


$t->diag('The before-all callbacks are called before the whole test suite');

  // fixtures
  $mock = LimeMock::createStrict('Mock', $t);
  $r = new LimeTestRunner();
  $r->addBeforeAll(array($mock, 'setUp'));
  $r->addTest(array($mock, 'testDoSomething'));
  $r->addTest(array($mock, 'testDoSomethingElse'));
  $mock->setUp();
  $mock->testDoSomething();
  $mock->testDoSomethingElse();
  $mock->replay();
  // test
  $r->run();
  // assertions
  $mock->verify();


$t->diag('The after-all callbacks are called before the whole test suite');

  // fixtures
  $mock = LimeMock::createStrict('Mock', $t);
  $r = new LimeTestRunner();
  $r->addAfterAll(array($mock, 'tearDown'));
  $r->addTest(array($mock, 'testDoSomething'));
  $r->addTest(array($mock, 'testDoSomethingElse'));
  $mock->testDoSomething();
  $mock->testDoSomethingElse();
  $mock->tearDown();
  $mock->replay();
  // test
  $r->run();
  // assertions
  $mock->verify();


$t->diag('The error handlers are called when a test throws an error');

  // fixtures
  function throwError() { 1/0; }
  $mock = LimeMock::createStrict('Mock', $t);
  $r = new LimeTestRunner();
  $r->addTest('throwError');
  $r->addErrorHandler(array($mock, 'handleErrorFailed'));
  $r->addErrorHandler(array($mock, 'handleErrorSuccessful'));
  $mock->handleErrorFailed()->anyParameters()->returns(false);
  $mock->handleErrorSuccessful()->anyParameters()->returns(true);
  $mock->replay();
  // test
  $r->run();
  // assertions
  $mock->verify();


/*
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
*/


$t->diag('The exception handlers are called when a test throws an exception');

  // fixtures
  $mock = LimeMock::createStrict('Mock', $t);
  $r = new LimeTestRunner();
  $r->addTest(array($mock, 'testThrowsException'));
  $r->addExceptionHandler(array($mock, 'handleErrorFailed'));
  $r->addExceptionHandler(array($mock, 'handleErrorSuccessful'));
  $mock->testThrowsException()->throws('Exception');
  $mock->handleErrorFailed()->anyParameters()->returns(false);
  $mock->handleErrorSuccessful()->anyParameters()->returns(true);
  $mock->replay();
  // test
  $r->run();
  // assertions
  $mock->verify();


$t->diag('If no exception handler returns true, the exception is thrown again');

  // fixtures
  $mock = LimeMock::createStrict('Mock', $t);
  $r = new LimeTestRunner();
  $r->addTest(array($mock, 'testThrowsException'));
  $r->addExceptionHandler(array($mock, 'handleErrorFailed'));
  $mock->testThrowsException()->throws('Exception');
  $mock->handleErrorFailed()->anyParameters()->returns(false);
  $mock->replay();
  // test
  $t->expect('Exception');
  try
  {
    $r->run();
    $t->fail('The exception was thrown');
  }
  catch (Exception $e)
  {
    $t->pass('The exception was thrown');
  }
