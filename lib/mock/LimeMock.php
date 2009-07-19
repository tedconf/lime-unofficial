<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Generates mock objects
 *
 * This class generates configurable mock objects based on existing interfaces,
 * classes or virtual (non-existing) class names. You can use it to create
 * objects of classes that you have not implemented yet, or to substitute
 * a class in a test.
 *
 * A mock object is created with the create() method:
 *
 * <code>
 *   $mock = LimeMock::create('MyClass');
 * </code>
 *
 * Initially the mock is in recording mode. In this mode you just make the
 * expected method calls with the expected parameters. You can use modifiers
 * to configure return values or exceptions that should be thrown.
 *
 * <code>
 *   // method "someMethod()" returns "return value" when called with "parameter"
 *   $mock->someMethod('parameter')->returns('return value');
 * </code>
 *
 * Currently the following method modifiers are supported:
 *
 *   * returns(): The value that should be returned by the method
 *   * throws():  The exception name that should be thrown by the method
 *   * times():   The number of times the method should be called. Can be
 *                combined with the other modifiers
 *
 * <code>
 *   // method "add" will be called 5 times and return 3 every time
 *   $mock->add(1, 2)->returns(3)->times(5);
 * </code>
 *
 * Once the recording is over, you must call the method replay() on the mock.
 * After the call to this method, the mock is in replay mode. In this mode, it
 * listens for method calls and returns the results configured before.
 *
 * <code>
 *   $mock = LimeMock::create('MyClass');
 *   $mock->add(1, 2)->returns(3);
 *   $mock->replay();
 *
 *   echo $mock->add(1, 2);
 *   // returns 3
 * </code>
 *
 * This functionality is perfect to substitute real classes by fake
 * implementations.
 *
 * You also have the possibility to find out whether all the configured
 * methods have been called with the right parameters while in replay mode
 * by calling verify(). This method requires a LimeTest object to store
 * the results of the tests. The LimeTest object must be passed to create()
 * when creating the new mock.
 *
 * <code>
 *   $mock = LimeMock::create('MyClass', $limeTest);
 *   $mock->add(1,2);
 *   $mock->reply();
 *   $mock->add(1);
 *   $mock->verify();
 *
 *   // results in a failing test
 * </code>
 *
 * Usually, configured and actual method parameters are compared with PHP's
 * usual weak typing. If you want to enforce strict typing, you must call
 * the method setStrict() on the mock.
 *
 * <code>
 *   $mock = LimeMock::create('MyClass', $limeTest);
 *   $mock->setStrict();
 *   $mock->doSomething(1);
 *   $mock->replay();
 *   $mock->doSomething('1');
 *   $mock->verify();
 *
 *   // results in a failing test
 * </code>
 *
 * If an unexpected method is called, you usually find that out in the call
 * to verify() that compares all expected method calls with actual method calls.
 * If you want to debug were a certain unexpected method call comes from, you
 * should call setFailOnVerify() on the mock. In this mode an exception is
 * thrown once an unconfigured method is called while in replay mode.
 *
 * As for verify(), setFailOnVerify() requires a LimeTest instance to be
 * present.
 *
 * <code>
 *   $mock = LimeMock::create('MyClass', $limeTest);
 *   $mock->doSomething();
 *   $mock->replay();
 *   $mock->doSomethingElse(); // throws a lime_expectation_exception
 * </code>
 *
 * As you have seen, mock objects offer a few methods that cannot be mocked
 * by default. Those are:
 *
 *   * verify()
 *   * replay()
 *   * setStrict()
 *   * setFailOnVerify()
 *
 * If you need to mock any of these methods, you need to set the third
 * parameter $generateMethods to false when calling create(). Instead of calling
 * these methods on the mock, you will need to call them statically in LimeMock
 * and need to pass the mock as first argument.
 *
 * <code>
 *   $mock = LimeMock::create('MyClass', $limeTest, false);
 *   $mock->replay()->returns('Response of replay()');
 *   LimeMock::replay($mock);
 *
 *   echo $mock->replay();
 *   // echos "Response of replay()"
 * </code>
 *
 * @package    lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 *
 */
class LimeMock
{

  /**
   * A template for overridden abstract methods in base classes/interfaces.
   * @var string
   */
  protected static $methodTemplate = '%s function %s(%s) { $args = func_get_args(); return $this->__call(\'%s\', $args); }';

  protected static $parameterTemplate = '%s %s';

  protected static $parameterWithDefaultTemplate = '%s %s = %s';

  protected static $illegalMethods = array(
    '__construct',
    '__call',
    '__lime_replay',
    '__lime_getState',
  );

  /**
   * Creates a new mock object for the given class or interface name.
   *
   * The class/interface does not necessarily have to exist. Each mock object
   * generated with LimeMock::create($class) fulfills the condition
   * ($mock instanceof $class).
   *
   * If you want to verify this object, you need to pass a LimeTest instance
   * as well. Use the third parameter $generateMethods to suppress the generation
   * of the magic methods replay(), verify() etc. See the description of this
   * class for more information.
   *
   * @param  string     $classOrInterface  The (non-)existing class/interface
   *                                       you want to mock
   * @param  LimeTest  $test              The test instance
   * @param  bool       $generateMethods   Whether magic methods should be generated
   * @return LimeMockInterface           The mock object
   */
  public static function create($classOrInterface, $test = null, $generateMethods = true)
  {
    $name = self::generateClass($classOrInterface, $generateMethods);
    $output = $test instanceof LimeOutputInterface ? $test : ($test ? $test->getOutput() : null);

    return new $name($classOrInterface, new LimeMockUnorderedBehaviour(), $output);
  }

  public static function createStrict($classOrInterface, $test = null, $generateMethods = true)
  {
    $name = self::generateClass($classOrInterface, $generateMethods);
    $output = $test instanceof LimeOutputInterface ? $test : ($test ? $test->getOutput() : null);

    return new $name($classOrInterface, new LimeMockOrderedBehaviour(), $output);
  }

  protected static function generateClass($classOrInterface, $generateMethods = true)
  {
    $methods = '';

    if (!class_exists($classOrInterface) && !interface_exists($classOrInterface))
    {
      eval(sprintf('interface %s {}', $classOrInterface));
    }

    $class = new ReflectionClass($classOrInterface);
    foreach ($class->getMethods() as $method)
    {
      if (!in_array($method->getName(), self::$illegalMethods))
      {
        /* @var $method ReflectionMethod */
        $modifiers = Reflection::getModifierNames($method->getModifiers());
        $modifiers = array_diff($modifiers, array('abstract'));
        $modifiers = implode(' ', $modifiers);

        $parameters = array();

        foreach ($method->getParameters() as $parameter)
        {
          $typeHint = '';

          /* @var $parameter ReflectionParameter */
          if ($parameter->getClass())
          {
            $typeHint = $parameter->getClass()->getName();
          }
          else if ($parameter->isArray())
          {
            $typeHint = 'array';
          }

          $name = '$'.$parameter->getName();

          if ($parameter->isOptional())
          {
            $default = var_export($parameter->getDefaultValue(), true);
            $parameters[] = sprintf(self::$parameterWithDefaultTemplate, $typeHint, $name, $default);
          }
          else
          {
            $parameters[] = sprintf(self::$parameterTemplate, $typeHint, $name);
          }
        }

        $methods .= sprintf(self::$methodTemplate, $modifiers, $method->getName(),
            implode(', ', $parameters), $method->getName());
      }
    }

    $interfaces = array();

    $name = self::generateName($class->getName());

    $declaration = 'class '.$name;

    if ($class->isInterface())
    {
      $interfaces[] = $class->getName();
    }
    else
    {
      $declaration .= ' extends '.$class->getName();
    }

    if ($generateMethods)
    {
      $interfaces[] = 'LimeMockInterface';
    }

    if (count($interfaces) > 0)
    {
      $declaration .= ' implements '.implode(', ', $interfaces);
    }

    $template = new LimeMockTemplate(dirname(__FILE__).'/template/mocked_class.tpl');

    eval($template->render(array(
      'class_declaration'   =>  $declaration,
      'methods'             =>  $methods,
      'generate_methods'    =>  $generateMethods,
    )));

    return $name;
  }

  /**
   * Generates a mock class name for the given original class/interface name.
   * @param  string $originalName
   * @return string
   */
  protected static function generateName($originalName)
  {
    while (!isset($name) || class_exists($name, false))
    {
      // inspired by PHPUnit_Framework_MockObject_Generator
      $name = 'Mock_'.$originalName.'_'.substr(md5(microtime()), 0, 8);
    }

    return $name;
  }

  /**
   * Turns the given mock into replay mode.
   * @param  $mock
   */
  public static function replay($mock)
  {
    return $mock->__lime_replay();
  }

  public static function reset($mock)
  {
    return $mock->__lime_getState()->reset();
  }

  /**
   * Sets the given mock to compare method parameters with strict typing.
   * @param  $mock
   */
  public static function setStrict($mock)
  {
    return $mock->__lime_getState()->setStrict();
  }

  /**
   * Configures the mock to throw an exception when an unexpected method call
   * is made.
   *
   * @param  $mock                       The mock object
   * @throws lime_expectation_exception  When an unexpected method is called
   */
  public static function setFailOnVerify($mock)
  {
    return $mock->__lime_getState()->setFailOnVerify();
  }

  /**
   * Configures the mock to expect no method call.
   */
  public static function setExpectNothing()
  {
    return $mock->__lime_getState()->setExpectNothing();
  }

  /**
   * Verifies the given mock.
   *
   * @param $mock  The mock object
   */
  public static function verify($mock)
  {
    return $mock->__lime_getState()->verify();
  }

}


