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
    $nbTests            = 0,
    $output             = null,
    $results            = array(),
    $options            = array(),
    $expectedException  = null,
    $expectedCode       = null,
    $actualException    = null,
    $actualCode         = null;

  static protected
    $allResults = array();

  public function __construct($plan = null, array $options = array())
  {
    $this->options = array_merge(array(
      'base_dir'     => null,
      'force_colors' => false,
      'output'       => null,
      'verbose'      => false,
    ), $options);

    $this->output = $this->options['output'] ? $this->options['output'] : new LimeOutput($this->options['force_colors']);
    $this->options['base_dir'] = realpath($this->options['base_dir']);

    $caller = $this->findCaller(debug_backtrace());
    self::$allResults[] = array(
      'file'  => $caller[0],
      'tests' => array(),
      'stats' => array('plan' => $plan, 'total' => 0, 'failed' => array(), 'passed' => array(), 'skipped' => array()),
    );

    $this->results = &self::$allResults[count(self::$allResults) - 1];

    null !== $plan and $this->output->echoln(sprintf("1..%d", $plan));
  }

  static public function reset()
  {
    self::$allResults = array();
  }

  static public function toArray()
  {
    return self::$allResults;
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

  public function __destruct()
  {
    $plan = $this->results['stats']['plan'];
    $passed = count($this->results['stats']['passed']);
    $failed = count($this->results['stats']['failed']);
    $total = $this->results['stats']['total'];
    is_null($plan) and $plan = $total and $this->output->echoln(sprintf("1..%d", $plan));

    if ($total > $plan)
    {
      $this->output->redBar(sprintf(" Looks like you planned %d tests but ran %d extra.", $plan, $total - $plan));
    }
    elseif ($total < $plan)
    {
      $this->output->redBar(sprintf(" Looks like you planned %d tests but only ran %d.", $plan, $total));
    }

    if ($failed)
    {
      $this->output->redBar(sprintf(" Looks like you failed %d tests of %d.", $failed, $passed + $failed));
    }
    else if ($total == $plan)
    {
      $this->output->greenBar(" Looks like everything went fine.");
    }

    flush();
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
    $this->updateStats();

    if ($result = (boolean) $exp)
    {
      $this->results['stats']['passed'][] = $this->nbTests;
    }
    else
    {
      $this->results['stats']['failed'][] = $this->nbTests;
    }
    $this->results['tests'][$this->nbTests]['message'] = $message;
    $this->results['tests'][$this->nbTests]['status'] = $result;
    $this->output->echoln(sprintf("%s %d%s", $result ? 'ok' : 'not ok', $this->nbTests, $message = $message ? sprintf('%s %s', 0 === strpos($message, '#') ? '' : ' -', $message) : ''));

    if (!$result)
    {
      $this->output->diag(sprintf('    Failed test (%s at line %d)', str_replace(getcwd(), '.', $this->results['tests'][$this->nbTests]['file']), $this->results['tests'][$this->nbTests]['line']));
    }

    return $result;
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

    if (!$result = $this->ok($value, $message))
    {
      $this->setLastTestErrors(array(sprintf("           got: %s", var_export($exp1, true)), sprintf("      expected: %s", var_export($exp2, true))));
    }

    return $result;
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
    if (!$result = $this->ok($exp1 != $exp2, $message))
    {
      $this->setLastTestErrors(array(sprintf("      %s", var_export($exp2, true)), '          ne', sprintf("      %s", var_export($exp2, true))));
    }

    return $result;
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
    if (!$result = $this->ok(preg_match($regex, $exp), $message))
    {
      $this->setLastTestErrors(array(sprintf("                    '%s'", $exp), sprintf("      doesn't match '%s'", $regex)));
    }

    return $result;
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
    if (!$result = $this->ok(!preg_match($regex, $exp), $message))
    {
      $this->setLastTestErrors(array(sprintf("               '%s'", $exp), sprintf("      matches '%s'", $regex)));
    }

    return $result;
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
    if (!$this->ok($result, $message))
    {
      $this->setLastTestErrors(array(sprintf("      %s", str_replace("\n", '', var_export($exp1, true))), sprintf("          %s", $op), sprintf("      %s", str_replace("\n", '', var_export($exp2, true)))));
    }

    return $result;
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
        $failedMessages[] = sprintf("      method '%s' does not exist", $method);
        $result = false;
      }
    }

    !$this->ok($result, $message);

    !$result and $this->setLastTestErrors($failedMessages);

    return $result;
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
    if (!$result = $this->ok($type == $class, $message))
    {
      $this->setLastTestErrors(array(sprintf("      variable isn't a '%s' it's a '%s'", $class, $type)));
    }

    return $result;
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
    if (!$result = $this->ok($this->testIsDeeply($exp1, $exp2), $message))
    {
      $this->setLastTestErrors(array(sprintf("           got: %s", str_replace("\n", '', var_export($exp1, true))), sprintf("      expected: %s", str_replace("\n", '', var_export($exp2, true)))));
    }

    return $result;
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
    return $this->ok(true, $message);
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
    return $this->ok(false, $message);
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
    $this->output->diag($message);
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
      $this->pass(sprintf("# SKIP%s", $message ? ' '.$message : ''));
      $this->results['stats']['skipped'][] = $this->nbTests;
      array_pop($this->results['stats']['passed']);
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
    $this->pass(sprintf("# TODO%s", $message ? ' '.$message : ''));
    $this->results['stats']['skipped'][] = $this->nbTests;
    array_pop($this->results['stats']['passed']);
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
    $this->expectedException  = $exception;
    $this->expectedCode       = $code;
    $this->actualException    = null;
    $this->actualCode         = null;
  }

  public function handleException(Exception $exception)
  {
    if (!is_null($this->expectedException))
    {
      $this->actualException = get_class($exception);
      $this->actualCode = $exception->getCode();

      return true;
    }
    else
    {
      // TODO: We should always return true to avoid errors being rethrown as
      // LimeError instances. When errors occur in the shutdown procedure,
      // throwing exceptions will result in new errors
      return false;
    }
  }

  public function verifyException()
  {
    if (!is_null($this->expectedException))
    {
      if (is_null($this->expectedCode))
      {
        $this->is($this->actualException, $this->expectedException, sprintf('A "%s" was thrown', $this->expectedException));
      }
      else
      {
        $actual = sprintf('%s (%s)', $this->actualException, var_export($this->actualCode, true));
        $expected = sprintf('%s (%s)', $this->expectedException, var_export($this->expectedCode, true));

        $this->is($actual, $expected, sprintf('A "%s" with code "%s" was thrown', $this->expectedException, $this->expectedCode));
      }
    }

    $this->expectedException = null;
  }

  protected function updateStats()
  {
    ++$this->nbTests;
    ++$this->results['stats']['total'];

    list($this->results['tests'][$this->nbTests]['file'], $this->results['tests'][$this->nbTests]['line']) = $this->findCaller(debug_backtrace());
  }

  protected function setLastTestErrors(array $errors)
  {
    $this->output->diag($errors);

    $this->results['tests'][$this->nbTests]['error'] = implode("\n", $errors);
  }

  protected function findCaller(array $traces)
  {
    $t = array_reverse($traces);
    foreach ($t as $trace)
    {
      if (isset($trace['object']) && $trace['object'] instanceof LimeTest && isset($trace['file']) && isset($trace['line']))
      {
        return array($trace['file'], $trace['line']);
      }
    }

    // return the first call
    $last = count($traces) - 1;
    $file = $traces[$last]['file'];
    if ($this->options['base_dir'])
    {
      $file = str_replace(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $this->options['base_dir']), '', str_replace(array('/', '\\'), $file));
    }
    return array($file, $traces[$last]['line']);
  }
}