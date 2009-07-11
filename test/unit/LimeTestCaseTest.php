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


class TestCase extends LimeTestCase
{
  public $methodCalls;

  public function __construct()
  {
    parent::__construct(null, array('output' => new LimeOutputNone()));
  }

  public function setUp()
  {
    $this->methodCalls->addActual('setUp');
  }

  public function tearDown()
  {
    $this->methodCalls->addActual('tearDown');
  }

  public function testDoSomething()
  {
    $this->methodCalls->addActual('testDoSomething');
  }

  public function testDoSomethingElse()
  {
    $this->methodCalls->addActual('testDoSomethingElse');
  }
}


$t = new LimeAssert(1);


$t->diag('The methods setUp() and tearDown() are called before and after each test method');

  // fixtures
  $test = new TestCase();
  $test->methodCalls = new LimeExpectationList($t);
  $test->methodCalls->addExpected('setUp');
  $test->methodCalls->addExpected('testDoSomething');
  $test->methodCalls->addExpected('tearDown');
  $test->methodCalls->addExpected('setUp');
  $test->methodCalls->addExpected('testDoSomethingElse');
  $test->methodCalls->addExpected('tearDown');
  // test
  $test->run();
  // assertions
  $test->methodCalls->verify();
