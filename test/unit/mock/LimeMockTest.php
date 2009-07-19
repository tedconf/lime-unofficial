<?php

/*
 * This file is part of the symfony package.
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include dirname(__FILE__).'/../../bootstrap/unit.php';
require_once dirname(__FILE__).'/../../MockLimeTest.php';

LimeAnnotationSupport::enable();


interface TestInterface
{
  public function testMethod($parameter);
}

interface TestInterfaceWithTypeHints
{
  public function testMethod(stdClass $object, array $array);
}

interface TestInterfaceWithDefaultValues
{
  public function testMethod($null = null, $int = 1, $bool = true, $string = 'String', $float = 1.1);
}

abstract class TestClassAbstract
{
  abstract public function testMethod($parameter);
}

class TestClass
{
  public static $calls = 0;

  public function testMethod()
  {
    ++self::$calls;
  }
}

class TestException extends Exception {}


$t = new LimeTest(72);


// @Before

  $mockTest = new MockLimeTest();
  $m = LimeMock::create('TestClass', $mockTest);


// @After

  $mockTest = null;
  $m = null;


// @Test: Interfaces can be mocked

  // test
  $m = LimeMock::create('TestInterface');
  // assertions
  $t->ok($m instanceof TestInterface, 'The mock implements the interface');
  $t->ok($m instanceof LimeMockInterface, 'The mock implements "LimeMockInterface"');


// @Test: Abstract classes can be mocked

  // test
  $m = LimeMock::create('TestClassAbstract');
  // assertions
  $t->ok($m instanceof TestClassAbstract, 'The mock inherits the class');
  $t->ok($m instanceof LimeMockInterface, 'The mock implements "LimeMockInterface"');


// @Test: Non-existing classes can be mocked

  $m = LimeMock::create('FoobarClass');
  // assertions
  $t->ok($m instanceof FoobarClass, 'The mock generates and inherits the class');
  $t->ok($m instanceof LimeMockInterface, 'The mock implements "LimeMockInterface"');


// @Test: Methods with type hints can be mocked

  // test
  $m = LimeMock::create('TestInterfaceWithTypeHints');
  // assertions
  $t->ok($m instanceof TestInterfaceWithTypeHints, 'The mock implements the interface');


// @Test: Methods with default values can be mocked

  // test
  $m = LimeMock::create('TestInterfaceWithDefaultValues');
  // assertions
  $t->ok($m instanceof TestInterfaceWithDefaultValues, 'The mock implements the interface');


// @Test: Methods in the mocked class are not called

  // fixtures
  TestClass::$calls = 0;
  $m = LimeMock::create('TestClass');
  // test
  $m->testMethod();
  $m->replay();
  $m->testMethod();
  // assertions
  $t->is(TestClass::$calls, 0, 'The method has not been called');


// @Test: Return values can be stubbed

  // fixtures
  $m = LimeMock::create('TestClass');
  // test
  $m->testMethod()->returns('Foobar');
  $m->replay();
  $value = $m->testMethod();
  // assertions
  $t->is($value, 'Foobar', 'The correct value has been returned');


// @Test: Return values can be stubbed based on method parameters

  // fixtures
  $m = LimeMock::create('TestClass');
  // test
  $m->testMethod()->returns('Foobar');
  $m->testMethod(1)->returns('More foobar');
  $m->replay();
  $value1 = $m->testMethod();
  $value2 = $m->testMethod(1);
  // assertions
  $t->is($value1, 'Foobar', 'The correct value has been returned');
  $t->is($value2, 'More foobar', 'The correct value has been returned');


// @Test: Exceptions can be stubbed

  // fixtures
  $m = LimeMock::create('TestClass');
  $m->testMethod()->throws('TestException');
  $m->replay();
  $t->expect('TestException');
  // test
  $m->testMethod();


// @Test: ->verify() throws an exception if the mock has been created without a lime_test

  // fixtures
  $m = LimeMock::create('TestClass');
  $m->testMethod();
  $t->expect('BadMethodCallException');
  // test
  $m->verify();


// @Test: ->verify() fails if a method was not called

  // test
  $m->testMethod();
  $m->replay();
  $m->verify();
  // assertions
  $t->is($mockTest->fails, 1, 'One test failed');
  $t->is($mockTest->passes, 0, 'No test passed');


// @Test: ->verify() passes if a method was called correctly

  // test
  $m->testMethod(1, 'Foobar');
  $m->replay();
  $m->testMethod(1, 'Foobar');
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: ->verify() passes if two methods were called correctly

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


// @Test: An exception is thrown if a method is called with wrong parameters

  // fixture
  $m->testMethod(1, 'Foobar');
  $m->replay();
  $t->expect('LimeAssertionException');
  // test
  $m->testMethod(1);


// @Test: An exception is thrown if a method is called with the right parameters in a wrong order

  // fixture
  $m->testMethod(1, 'Foobar');
  $m->replay();
  $t->expect('LimeAssertionException');
  // test
  $m->testMethod('Foobar', 1);


// @Test: setFailOnVerify() suppresses exceptions upon method calls

  // test
  $m->setFailOnVerify();
  $m->testMethod(1, 'Foobar');
  $m->replay();
  $m->testMethod('Foobar', 1);
  $m->verify();
  // assertions
  $t->is($mockTest->fails, 1, 'One test failed');
  $t->is($mockTest->passes, 0, 'No test passed');


// @Test: A method can be expected twice with different parameters

  // @Test: - Case 1: Insufficient method calls

  // test
  $m->testMethod();
  $m->testMethod('Foobar');
  $m->replay();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


  // @Test: - Case 2: Sufficient method calls

  // test
  $m->testMethod();
  $m->testMethod('Foobar');
  $m->replay();
  $m->testMethod();
  $m->testMethod('Foobar');
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 2, 'Two tests passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: Methods may be called in any order

  // test
  $m->testMethod1();
  $m->testMethod2();
  $m->replay();
  $m->testMethod2();
  $m->testMethod1();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 2, 'Two tests passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: Methods may be called any number of times

  // test
  $m->testMethod();
  $m->replay();
  $m->testMethod();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: By default, method parameters are compared with weak typing

  // test
  $m->testMethod(1);
  $m->replay();
  $m->testMethod('1');
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: ->setStrict()

  // @Test: - Case 1: Type comparison fails

  // fixture
  $m->setStrict();
  $m->testMethod(1);
  $m->replay();
  $t->expect('LimeAssertionException');
  // test
  $m->testMethod('1');


  // @Test: - Case 2: Type comparison passes

  // test
  $m->setStrict();
  $m->testMethod(1);
  $m->replay();
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: ->times()

  // @Test: - Case 1: Too few actual calls
  // test
  $m->testMethod(1)->times(2);
  $m->replay();
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');


  // @Test: - Case 2: Too many actual calls

  // fixture
  $m->testMethod(1)->times(2);
  $m->replay();
  $m->testMethod(1);
  $m->testMethod(1);
  $t->expect('LimeAssertionException');
  // test
  $m->testMethod(1);


  // @Test: - Case 3: Correct number

  // test
  $m->testMethod(1)->times(2);
  $m->replay();
  $m->testMethod(1);
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


  // @Test: - Case 4: Call with different parameters

  // fixture
  $m->testMethod(1)->times(2);
  $m->replay();
  $m->testMethod(1);
  $t->expect('LimeAssertionException');
  // test
  $m->testMethod();


// @Test: ->atLeastOnce()

  // @Test: - Case 1: Zero actual calls

  $m->testMethod(1)->atLeastOnce();
  $m->replay();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');

  // @Test: - Case 2: One actual call

  $m->testMethod(1)->atLeastOnce();
  $m->replay();
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');

  // @Test: - Case 3: Two actual calls

  $m->testMethod(1)->atLeastOnce();
  $m->replay();
  $m->testMethod(1);
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: ->times() and ->returns()

  // test
  $m->testMethod(1)->returns('Foobar')->times(2);
  $m->replay();
  $value1 = $m->testMethod(1);
  $value2 = $m->testMethod(1);
  // assertions
  $t->is($value1, 'Foobar', 'The first return value is correct');
  $t->is($value2, 'Foobar', 'The second return value is correct');


// @Test: ->withAnyParameters()

  // @Test: - Case 1: Correct parameters

  // test
  $m->testMethod()->anyParameters();
  $m->replay();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');

  // @Test: - Case 1: "Wrong" parameters

  // test
  $m->testMethod()->anyParameters();
  $m->replay();
  $m->testMethod(1, 2, 3);
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: ->between()

  // @Test: - Case 1: Too few calls

  // test
  $m->testMethod()->between(2, 4);
  $m->replay();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 1, 'One test failed');

  // @Test: - Case 2: Correct number

  // test
  $m->testMethod()->between(2, 4);
  $m->replay();
  $m->testMethod();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');

  // @Test: - Case 3: Another correct number

  // test
  $m->testMethod()->between(2, 4);
  $m->replay();
  $m->testMethod();
  $m->testMethod();
  $m->testMethod();
  $m->testMethod();
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');

  // @Test: - Case 4: Too many calls

  // test
  $m->testMethod()->between(2, 4);
  $m->replay();
  $m->testMethod();
  $m->testMethod();
  $m->testMethod();
  $m->testMethod();
  $t->expect('LimeAssertionException');
  $m->testMethod();


// @Test: ->never()

  // @Test: - Case 1: No actual call

  // test
  $m->testMethod(1, 2, 3);
  $m->testMethod()->never();
  $m->replay();
  $m->testMethod(1, 2, 3);
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 2, 'Two tests passed');
  $t->is($mockTest->fails, 0, 'No test failed');

  // @Test: - Case 2: Any actual calls

  // test
  $m->testMethod(1, 2, 3);
  $m->testMethod()->never();
  $m->replay();
  $m->testMethod(1, 2, 3);
  $t->expect('LimeAssertionException');
  $m->testMethod();


// @Test: ->strict() enforces strict parameter checks for single methods

  // @Test: - Case 1: Type comparison fails

  // fixture
  $m->testMethod(1)->strict();
  $m->replay();
  $t->expect('LimeAssertionException');
  // test
  $m->testMethod('1');


  // @Test: - Case 2: Type comparison passes

  // test
  $m->testMethod(1)->strict();
  $m->replay();
  $m->testMethod(1);
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 1, 'One test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: The control methods like ->replay() can be mocked

  // fixtures
  $m = LimeMock::create('TestClass', null, false);
  // test
  $m->replay()->returns('Foobar');
  LimeMock::replay($m);
  $value = $m->replay();
  // assertions
  $t->is($value, 'Foobar', 'The return value was correct');


// @Test: If no method call is expected, all method calls are ignored

  // test
  $m->replay();
  $m->testMethod1();
  $m->testMethod2(1, 'Foobar');
  $m->verify();
  // assertions
  $t->is($mockTest->passes, 0, 'No test passed');
  $t->is($mockTest->fails, 0, 'No test failed');


// @Test: If setExpectNothing() is called, no method must be called

  // fixture
  $m->setExpectNothing();
  $m->replay();
  $t->expect('LimeAssertionException');
  // test
  $m->testMethod();
