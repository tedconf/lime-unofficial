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
    return $this->isExpected;
  }
}


$t = new LimeTest(21);


// @Before

  $output = LimeMock::create('LimeOutputInterface', $t);
  $l = new TestExpectationCollection($output);


// @After

  $output = null;
  $l = null;


// @Test: No value expected, no value retrieved

  // test
  $output->invoke('pass')->once()->anyParameters();
  $output->invoke('fail')->never();
  $l->verify();
  // assertions
  $output->verify();


// @Test: One value expected, no value retrieved

  // fixtures
  $output = new MockLimeTest();
  $l = new TestExpectationCollection($output);
  // test
  $l->addExpected(1);
  $l->verify();
  // assertions
  $t->is($output->passes, 0, 'No test passed');
  $t->is($output->fails, 1, 'One test failed');


// @Test: One value expected, one different value retrieved

  // fixtures
  $output = new MockLimeTest();
  $l = new TestExpectationCollection($output);
  // test
  $l->addExpected(1);
  $l->addActual(2);
  $l->verify();
  // assertions
  $t->is($output->passes, 0, 'No test passed');
  $t->is($output->fails, 1, 'One test failed');


// @Test: No expectations are set, added values are ignored

  // fixtures
  $output = new MockLimeTest();
  $l = new TestExpectationCollection($output);
  // test
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: An exception is thrown if an unexpected value is added

  // fixtures
  $output = new MockLimeTest();
  $l = new TestExpectationCollection($output, false);
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


// @Test: Exactly no values are expected

  // fixtures
  $output = new MockLimeTest();
  $l = new TestExpectationCollection($output, false);
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


// @Test: The expected value was added

  // fixtures
  $output = new MockLimeTest();
  $l = new TestExpectationCollection($output);
  // test
  $l->addExpected(1);
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: The list can contain a mix of different types

  // fixtures
  $output = new MockLimeTest();
  $l = new TestExpectationCollection($output);
  // test
  $l->addExpected(1);
  $l->addExpected('Foobar');
  $l->addExpected(new stdClass());
  $l->addActual(1);
  $l->addActual('Foobar');
  $l->addActual(new stdClass());
  $l->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: By default, values are compared with weak typing

  // fixtures
  $output = new MockLimeTest();
  $l = new TestExpectationCollection($output);
  // test
  $l->addExpected(1);
  $l->addActual('1');
  $l->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: If you call setStrict(), values are compared with strict typing - different types

  // fixtures
  $output = new MockLimeTest();
  $l = new TestExpectationCollection($output);
  // test
  $l->setStrict();
  $l->addExpected(1);
  $l->addActual('1');
  $l->verify();
  // assertions
  $t->is($output->passes, 0, 'No test passed');
  $t->is($output->fails, 1, 'One test failed');


// @Test: If you call setStrict(), values are compared with strict typing - same types

  // fixtures
  $output = new MockLimeTest();
  $l = new TestExpectationCollection($output);
  // test
  $l->setStrict();
  $l->addExpected(1);
  $l->addActual(1);
  $l->verify();
  // assertions
  $t->is($output->passes, 1, 'One test passed');
  $t->is($output->fails, 0, 'No test failed');


// @Test: Calling verify() results in an exception if no test is set

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
