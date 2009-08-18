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
 * Unit test library.
 *
 * @package    lime
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @version    SVN: $Id$
 */
class LimeTest
{
  protected
    $output               = null,
    $options              = array(),
    $expectedException    = null,
    $expectedCode         = null,
    $actualException      = null,
    $actualCode           = null,
    $expectedExceptionAt  = null;

  public function __construct($plan = null, array $options = array())
  {
    $this->options = array(
      'base_dir'     => null,
      'output'       => 'tap',
      'force_colors' => false,
      'verbose'      => false,
      'serialize'    => false,
    );

    foreach (LimeShell::parseArguments($GLOBALS['argv']) as $argument => $value)
    {
      $this->options[str_replace('-', '_', $argument)] = $value;
    }

    $this->options = array_merge($this->options, $options);

    $this->options['base_dir'] = realpath($this->options['base_dir']);

    list ($file, $line) = LimeTrace::findCaller('LimeTest');

    if (is_string($this->options['output']))
    {
      $factory = new LimeOutputFactory($this->options);

      $this->output = $factory->create($this->options['output']);
    }
    else
    {
      $this->output = $this->options['output'];
    }

    $this->output->focus($file);

    if (!is_null($plan))
    {
      $this->output->plan($plan);
    }

    set_error_handler(array($this, 'handleError'));
    set_exception_handler(array($this, 'handleException'));
  }

  public function __destruct()
  {
    $this->output->close();
    $this->output->flush();

    restore_error_handler();
    restore_exception_handler();
  }

  public function getOutput()
  {
    return $this->output;
  }

  protected function test($condition, $message, $error = null)
  {
    list ($file, $line) = LimeTrace::findCaller('LimeTest');

    if ($result = (boolean) $condition)
    {
      $this->output->pass($message, $file, $line);
    }
    else
    {
      $this->output->fail($message, $file, $line, $error);
    }

    return $result;
  }

  /**
   * Tests a condition and passes if it is true
   *
   * @param mixed  $exp     condition to test
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function ok($exp, $message = '')
  {
    if ((boolean)$exp)
    {
      return $this->pass($message);
    }
    else
    {
      return $this->fail($message);
    }
  }

  /**
   * Compares two values and passes if they are equal (==)
   *
   * @param mixed  $exp1    left value
   * @param mixed  $exp2    right value
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function is($exp1, $exp2, $message = '')
  {
    $exp1 = LimeTester::create($exp1);
    $exp2 = LimeTester::create($exp2);

    try
    {
      $exp1->assertEquals($exp2);

      return $this->pass($message);
    }
    catch (LimeAssertionFailedException $e)
    {
      $error = sprintf("     got: %s\nexpected: %s", $e->getActual(10), $e->getExpected(10));

      return $this->fail($message, $error);
    }
  }

  /**
   * Compares two values and passes if they are identical (===)
   *
   * @param mixed  $exp1    left value
   * @param mixed  $exp2    right value
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function same($exp1, $exp2, $message = '')
  {
    $exp1 = LimeTester::create($exp1);
    $exp2 = LimeTester::create($exp2);

    try
    {
      $exp1->assertSame($exp2);

      return $this->pass($message);
    }
    catch (LimeAssertionFailedException $e)
    {
      $error = sprintf("     got: %s\nexpected: %s", $e->getActual(10), $e->getExpected(10));

      return $this->fail($message, $error);
    }
  }

  /**
   * Compares two values and passes if they are not equal
   *
   * @param mixed  $exp1    left value
   * @param mixed  $exp2    right value
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function isnt($exp1, $exp2, $message = '')
  {
    $exp1 = LimeTester::create($exp1);
    $exp2 = LimeTester::create($exp2);

    try
    {
      $exp1->assertNotEquals($exp2);

      return $this->pass($message);
    }
    catch (LimeAssertionFailedException $e)
    {
      $error = sprintf("%s\n    must not be\n%s", $e->getActual(), $e->getExpected());

      return $this->fail($message, $error);
    }
  }

  /**
   * Compares two values and passes if they are not identical (!==)
   *
   * @param mixed  $exp1    left value
   * @param mixed  $exp2    right value
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function isntSame($exp1, $exp2, $message = '')
  {
    $exp1 = LimeTester::create($exp1);
    $exp2 = LimeTester::create($exp2);

    try
    {
      $exp1->assertNotSame($exp2);

      return $this->pass($message);
    }
    catch (LimeAssertionFailedException $e)
    {
      $error = sprintf("%s\n    must not be\n%s", $e->getActual(), $e->getExpected());

      return $this->fail($message, $error);
    }
  }

  /**
   * Tests a string against a regular expression
   *
   * @param string $exp     value to test
   * @param string $regex   the pattern to search for, as a string
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function like($exp1, $exp2, $message = '')
  {
    $exp1 = LimeTester::create($exp1);
    $exp2 = LimeTester::create($exp2);

    try
    {
      $exp1->assertLike($exp2);

      return $this->pass($message);
    }
    catch (LimeAssertionFailedException $e)
    {
      $error = sprintf("              %s\ndoesn't match %s", $e->getActual(), $e->getExpected());

      return $this->fail($message, $error);
    }
  }

  /**
   * Checks that a string doesn't match a regular expression
   *
   * @param string $exp     value to test
   * @param string $regex   the pattern to search for, as a string
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function unlike($exp1, $exp2, $message = '')
  {
    $exp1 = LimeTester::create($exp1);
    $exp2 = LimeTester::create($exp2);

    try
    {
      $exp1->assertUnlike($exp2);

      return $this->pass($message);
    }
    catch (LimeAssertionFailedException $e)
    {
      $error = sprintf("         %s\nmatches %s", $e->getActual(), $e->getExpected());

      return $this->fail($message, $error);
    }
  }

  public function greaterThan($exp1, $exp2, $message = '')
  {
    $exp1 = LimeTester::create($exp1);
    $exp2 = LimeTester::create($exp2);

    try
    {
      $exp1->assertGreaterThan($exp2);

      return $this->pass($message);
    }
    catch (LimeAssertionFailedException $e)
    {
      $error = sprintf("         %s\nis not > %s", $e->getActual(), $e->getExpected());

      return $this->fail($message, $error);
    }
  }

  public function greaterThanEqual($exp1, $exp2, $message = '')
  {
    $exp1 = LimeTester::create($exp1);
    $exp2 = LimeTester::create($exp2);

    try
    {
      $exp1->assertGreaterThanOrEqual($exp2);

      return $this->pass($message);
    }
    catch (LimeAssertionFailedException $e)
    {
      $error = sprintf("          %s\nis not >= %s", $e->getActual(), $e->getExpected());

      return $this->fail($message, $error);
    }
  }

  public function lessThan($exp1, $exp2, $message = '')
  {
    $exp1 = LimeTester::create($exp1);
    $exp2 = LimeTester::create($exp2);

    try
    {
      $exp1->assertLessThan($exp2);

      return $this->pass($message);
    }
    catch (LimeAssertionFailedException $e)
    {
      $error = sprintf("         %s\nis not < %s", $e->getActual(), $e->getExpected());

      return $this->fail($message, $error);
    }
  }

  public function lessThanEqual($exp1, $exp2, $message = '')
  {
    $exp1 = LimeTester::create($exp1);
    $exp2 = LimeTester::create($exp2);

    try
    {
      $exp1->assertLessThanOrEqual($exp2);

      return $this->pass($message);
    }
    catch (LimeAssertionFailedException $e)
    {
      $error = sprintf("          %s\nis not <= %s", $e->getActual(), $e->getExpected());

      return $this->fail($message, $error);
    }
  }

  /**
   * Compares two arguments with an operator
   *
   * @param mixed  $exp1    left value
   * @param string $op      operator
   * @param mixed  $exp2    right value
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function compare($exp1, $op, $exp2, $message = '')
  {
    switch ($op)
    {
      case '===':
        return $this->same($exp1, $exp2, $message);
      case '!==':
        return $this->isntSame($exp1, $exp2, $message);
      case '==':
        return $this->is($exp1, $exp2, $message);
      case '!=':
        return $this->isnt($exp1, $exp2, $message);
      case '<':
        return $this->lessThan($exp1, $exp2, $message);
      case '<=':
        return $this->lessThanEqual($exp1, $exp2, $message);
      case '>':
        return $this->greaterThan($exp1, $exp2, $message);
      case '>=':
        return $this->greaterThanEqual($exp1, $exp2, $message);
      default:
        throw new InvalidArgumentException(sprintf('Unknown operation "%s"', $op));
    }
  }

  public function contains($exp1, $exp2, $message = '')
  {
    $exp1 = LimeTester::create($exp1);
    $exp2 = LimeTester::create($exp2);

    try
    {
      $exp1->assertContains($exp2);

      return $this->pass($message);
    }
    catch (LimeAssertionFailedException $e)
    {
      $error = sprintf("%s\n    doesn't contain\n%s", $e->getActual(), $e->getExpected());

      return $this->fail($message, $error);
    }
  }

  public function containsNot($exp1, $exp2, $message = '')
  {
    $exp1 = LimeTester::create($exp1);
    $exp2 = LimeTester::create($exp2);

    try
    {
      $exp1->assertNotContains($exp2);

      return $this->pass($message);
    }
    catch (LimeAssertionFailedException $e)
    {
      $error = sprintf("%s\n    must not contain\n%s", $e->getActual(), $e->getExpected());

      return $this->fail($message, $error);
    }
  }

  /**
   * Checks the availability of a method for an object or a class
   *
   * @param mixed        $object  an object instance or a class name
   * @param string|array $methods one or more method names
   * @param string       $message display output message when the test passes
   *
   * @return boolean
   */
  public function hasMethod($object, $methods, $message = '')
  {
    $result = true;
    $failedMessages = array();
    foreach ((array) $methods as $method)
    {
      if (!method_exists($object, $method))
      {
        $failedMessages[] = sprintf("method '%s' does not exist", $method);
        $result = false;
      }
    }

    return $this->test($result, $message, implode("\n", $failedMessages));
  }

  /**
   * Checks the type of an argument
   *
   * @param mixed  $var     variable instance
   * @param string $class   class or type name
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function isa($var, $class, $message = '')
  {
    $type = is_object($var) ? get_class($var) : gettype($var);
    $error = sprintf("variable isn't a '%s' it's a '%s'", $class, $type);

    return $this->test($type == $class, $message, $error);
  }

  /**
   * Checks that two arrays have the same values
   *
   * @param mixed  $exp1    first variable
   * @param mixed  $exp2    second variable
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function isDeeply($exp1, $exp2, $message = '')
  {
    return $this->is($exp1, $exp2, $message);
  }

  /**
   * Always passes--useful for testing exceptions
   *
   * @param string $message display output message
   *
   * @return true
   */
  public function pass($message = '')
  {
    list ($file, $line) = LimeTrace::findCaller('LimeTest');

    $this->output->pass($message, $file, $line);

    return true;
  }

  /**
   * Always fails--useful for testing exceptions
   *
   * @param string $message display output message
   *
   * @return false
   */
  public function fail($message = '', $error = null)
  {
    list ($file, $line) = LimeTrace::findCaller('LimeTest');

    $this->output->fail($message, $file, $line, $error);

    return false;
  }

  /**
   * Outputs a diag message but runs no test
   *
   * @param string $message display output message
   *
   * @return void
   */
  public function diag($message)
  {
    $this->output->comment($message);
  }

  /**
   * Counts as $nbTests tests--useful for conditional tests
   *
   * @param string  $message  display output message
   * @param integer $nbTests number of tests to skip
   *
   * @return void
   */
  public function skip($message = '', $nbTests = 1)
  {
    list ($file, $line) = LimeTrace::findCaller('LimeTest');

    for ($i = 0; $i < $nbTests; $i++)
    {
      $this->output->skip($message, $file, $line);
    }
  }

  /**
   * Counts as a test--useful for tests yet to be written
   *
   * @param string $message display output message
   *
   * @return void
   */
  public function todo($message = '')
  {
    list ($file, $line) = LimeTrace::findCaller('LimeTest');

    $this->output->todo($message, $file, $line);
  }

  public function comment($message)
  {
    $this->output->comment($message);
  }

  public function info($message)
  {
    $this->output->info($message);
  }

  public function expect($class, $code = null)
  {
    list ($file, $line) = LimeTrace::findCaller('LimeTest');

    if ($class instanceof Exception)
    {
      $this->expectedException = new LimeExpectedException(get_class($class), $class->getCode(), $file, $line);
    }
    else
    {
      $this->expectedException = new LimeExpectedException($class, $code, $file, $line);
    }

    $this->actualException = null;
  }

  public function handleError($code, $message, $file, $line, $context)
  {
    switch ($code)
    {
      case E_WARNING:
        $message = 'Warning: '.$message;
        break;
      case E_NOTICE:
        $message = 'Notice: '.$message;
        break;
    }

    $this->output->warning($message, $file, $line);

    return true;
  }

  public function handleException(Exception $exception)
  {
    if (!is_null($this->expectedException))
    {
      $this->actualException = $exception;
    }
    else
    {
      $this->output->error($exception);
    }

    return true;
  }

  public function verifyException()
  {
    if (!is_null($this->expectedException))
    {
      $expected = $this->expectedException;
      $actual = LimeExpectedException::create($this->actualException);

      if (is_null($expected->getCode()))
      {
        $message = sprintf('A "%s" was thrown', $expected->getClass());
      }
      else
      {
        $message = sprintf('A "%s" with code "%s" was thrown', $expected->getClass(), $expected->getCode());
      }

      $file = $expected->getFile();
      $line = $expected->getLine();

      if ($expected->equals($actual))
      {
        $this->output->pass($message, $file, $line);
      }
      else
      {
        $error = sprintf("     got: %s\nexpected: %s", $actual, $expected);
        $this->output->fail($message, $file, $line, $error);
      }
    }

    $this->expectedException = null;
  }
}