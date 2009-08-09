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

  public function __construct(LimeOutputInterface $test = null, $isExpected = true)
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

  // fixtures
  $output->any('pass')->once();
  $output->any('fail')->never();
  $output->replay();
  // test
  $l->verify();
  // assertions
  $output->verify();


// @Test: One value expected, no value retrieved

  // fixtures
  $output->any('pass')->never();
  $output->any('fail')->once();
  $output->replay();
  // test
  $l->addExpected(1);
  $l->verify();
  // assertions
  $output->verify();


// @Test: One value expected, one different value retrieved

  // fixtures
  $output->any('pass')->never();
  $output->any('fail')->once();
  $output->replay();
  // test
  $l->addExpected(1);
  $l->addActual(2);
  $l->verify();
  // assertions
  $output->verify();


// @Test: No expectations are set, added values are ignored

  // fixtures
  $output->any('pass')->once();
  $output->any('fail')->never();
  $output->replay();
  // test
  $l->addActual(1);
  $l->verify();
  // assertions
  $output->verify();


// @Test: An exception is thrown if an unexpected value is added

  // fixtures
  $l = new TestExpectationCollection($output, false);
  // test
  $l->addExpected('Foo');
  $t->expect('LimeAssertionException');
  $l->addActual('Bar');


// @Test: Exactly no values are expected

  // fixtures
  $l = new TestExpectationCollection($output, false);
  $l->setExpectNothing();
  $t->expect('LimeAssertionException');
  $l->addActual('Bar');


// @Test: The expected value was added

  // fixtures
  $output->any('pass')->once();
  $output->any('fail')->never();
  $output->replay();
  // test
  $l->addExpected(1);
  $l->addActual(1);
  $l->verify();
  // assertions
  $output->verify();


// @Test: The list can contain a mix of different types

  // fixtures
  $output->any('pass')->once();
  $output->any('fail')->never();
  $output->replay();
  // test
  $l->addExpected(1);
  $l->addExpected('Foobar');
  $l->addExpected(new stdClass());
  $l->addActual(1);
  $l->addActual('Foobar');
  $l->addActual(new stdClass());
  $l->verify();
  // assertions
  $output->verify();


// @Test: By default, values are compared with weak typing

  // fixtures
  $output->any('pass')->once();
  $output->any('fail')->never();
  $output->replay();
  // test
  $l->addExpected(1);
  $l->addActual('1');
  $l->verify();
  // assertions
  $output->verify();


// @Test: If you call setStrict(), values are compared with strict typing - different types

  // fixtures
  $output->any('pass')->never();
  $output->any('fail')->once();
  $output->replay();
  // test
  $l->setStrict();
  $l->addExpected(1);
  $l->addActual('1');
  $l->verify();
  // assertions
  $output->verify();


// @Test: If you call setStrict(), values are compared with strict typing - same types

  // fixtures
  $output->any('pass')->once();
  $output->any('fail')->never();
  $output->replay();
  // test
  $l->setStrict();
  $l->addExpected(1);
  $l->addActual(1);
  $l->verify();
  // assertions
  $output->verify();


// @Test: Calling verify() results in an exception if no test is set

  // fixtures
  $l = new TestExpectationCollection();
  // test
  $t->expect('BadMethodCallException');
  $l->verify();
