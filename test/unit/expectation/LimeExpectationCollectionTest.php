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


class TestExpectationCollection extends LimeExpectationCollection
{
  private $isExpected;

  public function __construct(LimeTest $test = null, $isExpected = true)
  {
    parent::__construct($test);

    $this->isExpected = $isExpected;
  }

  protected function isExpected($value)
  {
    return $this->isExpected;;
  }
}


$t = new LimeTest(21);


$t->diag('No value expected, no value retrieved');

  // fixtures
  $mock = new MockLimeTest();
  $l = new TestExpectationCollection($mock);
  // test
  $l->verify();
  // assertions
  $t->is($mock->passes, 1, 'One test passed');
  $t->is($mock->fails, 0, 'No test failed');


$t->diag('One value expected, no value retrieved');

  // fixtures
  $mock = new MockLimeTest();
  $l = new TestExpectationCollection($mock);
  // test
  $l->addExpected(1);
  $l->verify();
  // assertions
  $t->is($mock->passes, 0, 'No test passed');
  $t->is($mock->fails, 1, 'One test failed');


$t->diag('One value expected, one different value retrieved');

  // fixtures
  $mock = new MockLimeTest();
  $l = new TestExpectationCollection($mock);
  // test
  $l->addExpected(1);
  $l->addActual(2);
  $l->verify();
  // assertions
  $t->is($mock->passes, 0, 'No test passed');
  $t->is($mock->fails, 1, 'One test failed');


$t->diag('No expectations are set, added values are ignored');

  // fixtures
  $mock = new MockLimeTest();
  $l = new TestExpectationCollection($mock);
  // test
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($mock->passes, 1, 'One test passed');
  $t->is($mock->fails, 0, 'No test failed');


$t->diag('An exception is thrown if an unexpected value is added');

  // fixtures
  $mock = new MockLimeTest();
  $l = new TestExpectationCollection($mock, false);
  $l->addExpected('Foo');
  // test
  try
  {
    $l->addActual('Bar');
    $t->fail('A "LimeAssertionException" is thrown');
  }
  catch (LimeAssertionException $e)
  {
    $t->pass('A "LimeAssertionException" is thrown');
  }


$t->diag('Exactly no values are expected');

  // fixtures
  $mock = new MockLimeTest();
  $l = new TestExpectationCollection($mock, false);
  $l->setExpectNothing();
  // test
  try
  {
    $l->addActual('Bar');
    $t->fail('A "LimeAssertionException" is thrown');
  }
  catch (LimeAssertionException $e)
  {
    $t->pass('A "LimeAssertionException" is thrown');
  }


$t->diag('The expected value was added');

  // fixtures
  $mock = new MockLimeTest();
  $l = new TestExpectationCollection($mock);
  // test
  $l->addExpected(1);
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($mock->passes, 1, 'One test passed');
  $t->is($mock->fails, 0, 'No test failed');


$t->diag('The list can contain a mix of different types');

  // fixtures
  $mock = new MockLimeTest();
  $l = new TestExpectationCollection($mock);
  // test
  $l->addExpected(1);
  $l->addExpected('Foobar');
  $l->addExpected(new stdClass());
  $l->addActual(1);
  $l->addActual('Foobar');
  $l->addActual(new stdClass());
  $l->verify();
  // assertions
  $t->is($mock->passes, 1, 'One test passed');
  $t->is($mock->fails, 0, 'No test failed');


$t->diag('By default, values are compared with weak typing');

  // fixtures
  $mock = new MockLimeTest();
  $l = new TestExpectationCollection($mock);
  // test
  $l->addExpected(1);
  $l->addActual('1');
  $l->verify();
  // assertions
  $t->is($mock->passes, 1, 'One test passed');
  $t->is($mock->fails, 0, 'No test failed');


$t->diag('If you call setStrict(), values are compared with strict typing - different types');

  // fixtures
  $mock = new MockLimeTest();
  $l = new TestExpectationCollection($mock);
  // test
  $l->setStrict();
  $l->addExpected(1);
  $l->addActual('1');
  $l->verify();
  // assertions
  $t->is($mock->passes, 0, 'No test passed');
  $t->is($mock->fails, 1, 'One test failed');


$t->diag('If you call setStrict(), values are compared with strict typing - same types');

  // fixtures
  $mock = new MockLimeTest();
  $l = new TestExpectationCollection($mock);
  // test
  $l->setStrict();
  $l->addExpected(1);
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($mock->passes, 1, 'One test passed');
  $t->is($mock->fails, 0, 'No test failed');


$t->diag('Calling verify() results in an exception if no test is set');

  // fixtures
  $l = new TestExpectationCollection();
  // test
  try
  {
    $l->verify();
    $t->fail('A "BadMethodCallException" is thrown');
  }
  catch (BadMethodCallException $e)
  {
    $t->pass('A "BadMethodCallException" is thrown');
  }
