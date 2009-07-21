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
  const
    EPSILON = 0.0000000001;

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
    $this->options = array_merge(array(
      'base_dir'     => null,
      'force_colors' => false,
      'output'       => null,
      'verbose'      => false,
    ), $options);

    list ($file, $line) = self::findCaller();

    $this->output = $this->options['output'] ? $this->options['output'] : $this->getDefaultOutput($this->options['force_colors']);

    if (!is_null($plan))
    {
      $this->output->plan($plan, $file);
    }

    $this->options['base_dir'] = realpath($this->options['base_dir']);

    set_error_handler(array($this, 'handleError'));
    set_exception_handler(array($this, 'handleException'));
  }

  public function __destruct()
  {
    $this->output->flush();

    restore_error_handler();
    restore_exception_handler();
  }

  protected function getDefaultOutput($forceColors = false)
  {
    if (in_array('--raw', $GLOBALS['argv']))
    {
      return new LimeOutputRaw();
    }
    else if (in_array('--xml', $GLOBALS['argv']))
    {
      return new LimeOutputXml();
    }
    else if (in_array('--array', $GLOBALS['argv']))
    {
      $serialize = in_array('--serialize', $GLOBALS['argv']);

      return new LimeOutputArray($serialize);
    }
    else
    {
      $colorizer = LimeColorizer::isSupported() || $forceColors ? new LimeColorizer() : null;

      return new LimeOutputConsoleDetailed(new LimePrinter($colorizer));
    }
  }

  public function getOutput()
  {
    return $this->output;
  }

  static public function toXml($results = null)
  {
    if (is_null($results))
    {
      $results = self::$allResults;
    }

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;
    $dom->appendChild($testsuites = $dom->createElement('testsuites'));

    $errors = 0;
    $failures = 0;
    $errors = 0;
    $skipped = 0;
    $assertions = 0;

    foreach ($results as $result)
    {
      $testsuites->appendChild($testSuite = $dom->createElement('testsuite'));
      $testSuite->setAttribute('name', basename($result['file'], '.php'));
      $testSuite->setAttribute('file', $result['file']);
      $testSuite->setAttribute('failures', count($result['stats']['failed']));
      $testSuite->setAttribute('errors', 0);
      $testSuite->setAttribute('skipped', count($result['stats']['skipped']));
      $testSuite->setAttribute('tests', $result['stats']['plan']);
      $testSuite->setAttribute('assertions', $result['stats']['plan']);

      $failures += count($result['stats']['failed']);
      $skipped += count($result['stats']['skipped']);
      $assertions += $result['stats']['plan'];

      foreach ($result['tests'] as $test)
      {
        $testSuite->appendChild($testCase = $dom->createElement('testcase'));
        $testCase->setAttribute('name', $test['message']);
        $testCase->setAttribute('file', $test['file']);
        $testCase->setAttribute('line', $test['line']);
        $testCase->setAttribute('assertions', 1);
        if (!$test['status'])
        {
          $testCase->appendChild($failure = $dom->createElement('failure'));
          $failure->setAttribute('type', 'lime');
          if ($test['error'])
          {
            $failure->appendChild($dom->createTextNode($test['error']));
          }
        }
      }
    }

    $testsuites->setAttribute('failures', $failures);
    $testsuites->setAttribute('errors', $errors);
    $testsuites->setAttribute('tests', $assertions);
    $testsuites->setAttribute('assertions', $assertions);
    $testsuites->setAttribute('skipped', $skipped);

    return $dom->saveXml();
  }

  static protected function findCaller()
  {
    $traces = debug_backtrace();

    $t = array_reverse($traces);
    foreach ($t as $trace)
    {
      if (isset($trace['object']) && $trace['object'] instanceof LimeTest && isset($trace['file']) && isset($trace['line']))
      {
        return array($trace['file'], $trace['line']);
      }
    }

    // return the first call
    $file = $traces[0]['file'];
    if ($this->options['base_dir'])
    {
      $file = str_replace(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $this->options['base_dir']), '', str_replace(array('/', '\\'), $file));
    }

    return array($file, $traces[0]['line']);
  }

  protected function test($condition, $message, $error = null)
  {
    list ($file, $line) = $this->findCaller(debug_backtrace());

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
    return $this->test($exp, $message);
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
    if (is_object($exp1) || is_object($exp2))
    {
      $value = $exp1 === $exp2;
    }
    else if (is_float($exp1) && is_float($exp2))
    {
      $value = abs($exp1 - $exp2) < self::EPSILON;
    }
    else
    {
      $value = $exp1 == $exp2;
    }

    $error = sprintf("     got: %s\nexpected: %s", var_export($exp1, true), var_export($exp2, true));

    return $this->test($value, $message, $error);
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
    $error = sprintf("%s\n    ne\n%s", var_export($exp2, true), var_export($exp2, true));

    return $this->test($exp1 != $exp2, $message, $error);
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
  public function like($exp, $regex, $message = '')
  {
    $error = sprintf("              '%s'\ndoesn't match '%s'", $exp, $regex);

    return $this->test(preg_match($regex, $exp), $message, $error);
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
  public function unlike($exp, $regex, $message = '')
  {
    $error = sprintf("         '%s'\nmatches '%s'", $exp, $regex);

    return $this->test(!preg_match($regex, $exp), $message, $error);
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
    eval(sprintf("\$result = \$exp1 $op \$exp2;"));

    $error = sprintf("%s\n    %s\n%s", str_replace("\n", '', var_export($exp1, true)), $op, str_replace("\n", '', var_export($exp2, true)));

    return $this->test($result, $message, $error);
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
    $error = sprintf("     got: %s\nexpected: %s", str_replace("\n", '', var_export($exp1, true)), str_replace("\n", '', var_export($exp2, true)));

    return $this->test($this->testIsDeeply($exp1, $exp2), $message, $error);
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
    return $this->test(true, $message);
  }

  /**
   * Always fails--useful for testing exceptions
   *
   * @param string $message display output message
   *
   * @return false
   */
  public function fail($message = '')
  {
    return $this->test(false, $message);
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
    for ($i = 0; $i < $nbTests; $i++)
    {
      list ($file, $line) = $this->findCaller();

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
    $this->skip(trim('TODO '.$message));
  }

  private function testIsDeeply($var1, $var2)
  {
    if (gettype($var1) != gettype($var2))
    {
      return false;
    }

    if (is_array($var1))
    {
      ksort($var1);
      ksort($var2);

      $keys1 = array_keys($var1);
      $keys2 = array_keys($var2);
      if (array_diff($keys1, $keys2) || array_diff($keys2, $keys1))
      {
        return false;
      }
      $isEqual = true;
      foreach ($var1 as $key => $value)
      {
        $isEqual = $this->testIsDeeply($var1[$key], $var2[$key]);
        if ($isEqual === false)
        {
          break;
        }
      }

      return $isEqual;
    }
    else
    {
      return $var1 === $var2;
    }
  }

  public function comment($message)
  {
    $this->output->comment($message);
  }

  public function info($message)
  {
    $this->output->info($message);
  }

  public function error($message)
  {
    $this->output->error($message);
  }

  public function expect($exception, $code = null)
  {
    $this->expectedExceptionAt  = self::findCaller();
    $this->expectedException    = $exception;
    $this->expectedCode         = $code;
    $this->actualException      = null;
    $this->actualCode           = null;
  }

  protected function trimPath($path)
  {
    if (array_key_exists('base_dir', $this->options))
    {
      $path = str_replace($this->options['base_dir'], '', $path);
    }

    return $path;
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

    $this->output->warning($message, $this->trimPath($file), $line);

    return true;
  }

  public function handleException(Exception $exception)
  {
    if (!is_null($this->expectedException))
    {
      $this->actualException = get_class($exception);
      $this->actualCode = $exception->getCode();
    }
    else
    {
      $message = get_class($exception).': '.$exception->getMessage();

      $this->output->error($message, $this->trimPath($exception->getFile()), $exception->getLine());
    }

    return true;
  }

  public function verifyException()
  {
    if (!is_null($this->expectedException))
    {
      if (is_null($this->expectedCode))
      {
        $actual = $this->actualException;
        $expected = $this->expectedException;
        $message = sprintf('A "%s" was thrown', $this->expectedException);
      }
      else
      {
        $actual = sprintf('%s (%s)', $this->actualException, var_export($this->actualCode, true));
        $expected = sprintf('%s (%s)', $this->expectedException, var_export($this->expectedCode, true));
        $message = sprintf('A "%s" with code "%s" was thrown', $this->expectedException, $this->expectedCode);
      }

      list ($file, $line) = $this->expectedExceptionAt;

      if ($actual == $expected)
      {
        $this->output->pass($message, $file, $line);
      }
      else
      {
        $error = sprintf("     got: %s\nexpected: %s", is_null($this->actualException) ? 'none' : $actual, $expected);
        $this->output->fail($message, $file, $line, $error);
      }
    }

    $this->expectedException = null;
  }
}