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

  public function handleExceptionSuccessful(Exception $error)
  {
    $this->methodCalls->addActual('handleExceptionSuccessful');

    return true;
  }

  public function handleExceptionFailed(Exception $error)
  {
    $this->methodCalls->addActual('handleExceptionFailed');

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


$t = new LimeTest(21);


$t->diag('The test comments are printed');

  // fixtures
  $output = $t->mock('LimeOutputInterface');
  $output->comment('A test comment');
  $output->replay();
  $stub = $t->stub('Stub');
  $stub->testDoSomething();
  $stub->replay();
  $r = new LimeTestRunner($output);
  $r->addTest(array($stub, 'testDoSomething'), 'A test comment');
  // test
  $r->run();
  // assertions
  $output->verify();


$t->diag('The before callbacks are called before each test method');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
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
  $mock = $t->mock('Mock', array('strict' => true));
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
  $mock = $t->mock('Mock', array('strict' => true));
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
  $mock = $t->mock('Mock', array('strict' => true));
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


$t->diag('The exception handlers are called when a test throws an exception');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $r = new LimeTestRunner();
  $r->addTest(array($mock, 'testThrowsException'));
  $r->addExceptionHandler(array($mock, 'handleExceptionFailed'));
  $r->addExceptionHandler(array($mock, 'handleExceptionSuccessful'));
  $mock->testThrowsException()->throws('Exception');
  $mock->any('handleExceptionFailed')->returns(false);
  $mock->any('handleExceptionSuccessful')->returns(true);
  $mock->replay();
  // test
  $r->run();
  // assertions
  $mock->verify();


$t->diag('If no exception handler returns true, the exception is thrown again');

  // fixtures
  $mock = $t->mock('Mock', array('strict' => true));
  $r = new LimeTestRunner();
  $r->addTest(array($mock, 'testThrowsException'));
  $r->addExceptionHandler(array($mock, 'handleExceptionFailed'));
  $mock->testThrowsException()->throws('Exception');
  $mock->any('handleExceptionFailed')->returns(false);
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
