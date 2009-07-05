<?php

/**
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Unit test library.
 *
 * @package    lime
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @version    SVN: $Id$
 */
class sfLimeTest
{
  const EPSILON = 0.0000000001;

  protected $nbTests = 0;
  protected $output  = null;
  protected $results = array();
  protected $options = array();

  static protected $allResults = array();

  public function __construct($plan = null, $options = array())
  {
    // for BC
    if (!is_array($options))
    {
      $options = array('output' => $options);
    }

    $this->options = array_merge(array(
      'base_dir'     => null,
      'force_colors' => false,
      'output'       => null,
      'verbose'      => false,
    ), $options);

    $this->output = $this->options['output'] ? $this->options['output'] : new sfLimeOutput($this->options['force_colors']);
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

  /**
   * Validates that a file exists and that it is properly included
   *
   * @param string $file    file path
   * @param string $message display output message when the test passes
   *
   * @return boolean
   */
  public function includeOk($file, $message = '')
  {
    if (!$result = $this->ok((@include($file)) == 1, $message))
    {
      $this->setLastTestErrors(array(sprintf("      Tried to include '%s'", $file)));
    }

    return $result;
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

  protected function findCaller($traces)
  {
    // find the first call to a method of an object that is an instance of sfLimeTest
    $t = array_reverse($traces);
    foreach ($t as $trace)
    {
      if (isset($trace['object']) && $trace['object'] instanceof sfLimeTest)
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

class sfLimeOutput
{
  public $colorizer = null;

  public function __construct($forceColors = false)
  {
    $this->colorizer = new sfLimeColorizer($forceColors);
  }

  public function diag()
  {
    $messages = func_get_args();
    foreach ($messages as $message)
    {
      echo $this->colorizer->colorize('# '.join("\n# ", (array) $message), 'COMMENT')."\n";
    }
  }

  public function comment($message)
  {
    echo $this->colorizer->colorize(sprintf('# %s', $message), 'COMMENT')."\n";
  }

  public function info($message)
  {
    echo $this->colorizer->colorize(sprintf('> %s', $message), 'INFO_BAR')."\n";
  }

  public function error($message)
  {
    echo $this->colorizer->colorize(sprintf(' %s ', $message), 'redBar')."\n";
  }

  public function echoln($message, $colorizerParameter = null, $colorize = true)
  {
    if ($colorize)
    {
      $message = preg_replace('/(?:^|\.)((?:not ok|dubious) *\d*)\b/e', '$this->colorizer->colorize(\'$1\', \'ERROR\')', $message);
      $message = preg_replace('/(?:^|\.)(ok *\d*)\b/e', '$this->colorizer->colorize(\'$1\', \'INFO\')', $message);
      $message = preg_replace('/"(.+?)"/e', '$this->colorizer->colorize(\'$1\', \'PARAMETER\')', $message);
      $message = preg_replace('/(\->|\:\:)?([a-zA-Z0-9_]+?)\(\)/e', '$this->colorizer->colorize(\'$1$2()\', \'PARAMETER\')', $message);
    }

    echo ($colorizerParameter ? $this->colorizer->colorize($message, $colorizerParameter) : $message)."\n";
  }

  public function greenBar($message)
  {
    echo $this->colorizer->colorize($message.str_repeat(' ', 71 - min(71, strlen($message))), 'greenBar')."\n";
  }

  public function redBar($message)
  {
    echo $this->colorizer->colorize($message.str_repeat(' ', 71 - min(71, strlen($message))), 'redBar')."\n";
  }
}

class sfLimeOutputColor extends sfLimeOutput
{
}

class sfLimeColorizer
{
  static public $styles = array();

  protected $forceColors = false;

  public function __construct($forceColors = false)
  {
    $this->forceColors = $forceColors;
  }

  public static function style($name, $options = array())
  {
    self::$styles[$name] = $options;
  }

  public function colorize($text = '', $parameters = array())
  {
    // disable colors if not supported (windows or non tty console)
    if (!$this->forceColors && (DIRECTORY_SEPARATOR == '\\' || !function_exists('posix_isatty') || !@posix_isatty(STDOUT)))
    {
      return $text;
    }

    static $options    = array('bold' => 1, 'underscore' => 4, 'blink' => 5, 'reverse' => 7, 'conceal' => 8);
    static $foreground = array('black' => 30, 'red' => 31, 'green' => 32, 'yellow' => 33, 'blue' => 34, 'magenta' => 35, 'cyan' => 36, 'white' => 37);
    static $background = array('black' => 40, 'red' => 41, 'green' => 42, 'yellow' => 43, 'blue' => 44, 'magenta' => 45, 'cyan' => 46, 'white' => 47);

    !is_array($parameters) && isset(self::$styles[$parameters]) and $parameters = self::$styles[$parameters];

    $codes = array();
    isset($parameters['fg']) and $codes[] = $foreground[$parameters['fg']];
    isset($parameters['bg']) and $codes[] = $background[$parameters['bg']];
    foreach ($options as $option => $value)
    {
      isset($parameters[$option]) && $parameters[$option] and $codes[] = $value;
    }

    return "\033[".implode(';', $codes).'m'.$text."\033[0m";
  }
}

sfLimeColorizer::style('ERROR', array('bg' => 'red', 'fg' => 'white', 'bold' => true));
sfLimeColorizer::style('INFO', array('fg' => 'green', 'bold' => true));
sfLimeColorizer::style('PARAMETER', array('fg' => 'cyan'));
sfLimeColorizer::style('COMMENT', array('fg' => 'yellow'));

sfLimeColorizer::style('greenBar', array('fg' => 'white', 'bg' => 'green', 'bold' => true));
sfLimeColorizer::style('redBar', array('fg' => 'white', 'bg' => 'red', 'bold' => true));
sfLimeColorizer::style('INFO_BAR', array('fg' => 'cyan', 'bold' => true));

class sfLimeHarness extends sfLimeRegistration
{
  public $options = array();
  public $executable = null;
  public $stats   = array();
  public $output  = null;

  public function __construct($options = array())
  {
    // for BC
    if (!is_array($options))
    {
      $options = array('output' => $options);
    }

    $this->options = array_merge(array(
      'executable'   => null,
      'force_colors' => false,
      'output'       => null,
      'verbose'      => false,
    ), $options);

    $this->executable = $this->findExecutable($this->options['executable']);
    $this->output = $this->options['output'] ? $this->options['output'] : new sfLimeOutput($this->options['force_colors']);
  }

  protected function findExecutable($executable = null)
  {
    if (is_null($executable))
    {
      if (getenv('PHP_PATH'))
      {
        $executable = getenv('PHP_PATH');

        if (!is_executable($executable))
        {
          throw new Exception('The defined PHP_PATH environment variable is not a valid PHP executable.');
        }
      }
      else
      {
        $executable = PHP_BINDIR.DIRECTORY_SEPARATOR.'php';
      }
    }

    if (is_executable($executable))
    {
      return $executable;
    }

    $path = getenv('PATH') ? getenv('PATH') : getenv('Path');
    $extensions = DIRECTORY_SEPARATOR == '\\' ? (getenv('PATHEXT') ? explode(PATH_SEPARATOR, getenv('PATHEXT')) : array('.exe', '.bat', '.cmd', '.com')) : array('');
    foreach (array('php5', 'php') as $executable)
    {
      foreach ($extensions as $extension)
      {
        foreach (explode(PATH_SEPARATOR, $path) as $dir)
        {
          $file = $dir.DIRECTORY_SEPARATOR.$executable.$extension;
          if (is_executable($file))
          {
            return $file;
          }
        }
      }
    }

    throw new Exception("Unable to find PHP executable.");
  }

  public function toArray()
  {
    $results = array();
    foreach ($this->stats['files'] as $file => $stat)
    {
      $results = array_merge($results, $stat['output']);
    }

    return $results;
  }

  public function toXml()
  {
    return sfLimeTest::toXml($this->toArray());
  }

  public function run()
  {
    if (!count($this->files))
    {
      throw new Exception('You must register some test files before running them!');
    }

    // sort the files to be able to predict the order
    sort($this->files);

    $this->stats = array(
      'files'        => array(),
      'failed_files' => array(),
      'failed_tests' => 0,
      'total'        => 0,
    );

    foreach ($this->files as $file)
    {
      $this->stats['files'][$file] = array();
      $stats = &$this->stats['files'][$file];

      $relativeFile = $this->getRelativeFile($file);

      $testFile = tempnam(sys_get_temp_dir(), 'lime');
      $resultFile = tempnam(sys_get_temp_dir(), 'lime');
      file_put_contents($testFile, <<<EOF
<?php
include('$file');
file_put_contents('$resultFile', serialize(sfLimeTest::toArray()));
EOF
      );

      ob_start();
      // see http://trac.symfony-project.org/ticket/5437 for the explanation on the weird "cd" thing
      passthru(sprintf('cd & %s %s 2>&1', escapeshellarg($this->executable), escapeshellarg($testFile)), $return);
      ob_end_clean();
      unlink($testFile);

      $output = file_get_contents($resultFile);
      $stats['output'] = $output ? unserialize($output) : '';
      if (!$stats['output'])
      {
        $stats['output'] = array(array('file' => $file, 'tests' => array(), 'stats' => array('plan' => 1, 'total' => 1, 'failed' => array(0), 'passed' => array(), 'skipped' => array())));
      }
      unlink($resultFile);

      $fileStats = &$stats['output'][0]['stats'];

      $delta = 0;
      if ($return > 0)
      {
        $stats['status'] = 'dubious';
        $stats['status_code'] = $return;
      }
      else
      {
        $this->stats['total'] += $fileStats['total'];

        if (!$fileStats['plan'])
        {
          $fileStats['plan'] = $fileStats['total'];
        }

        $delta = $fileStats['plan'] - $fileStats['total'];
        if (0 != $delta)
        {
          $stats['status'] = 'dubious';
          $stats['status_code'] = 255;
        }
        else
        {
          $stats['status'] = $fileStats['failed'] ? 'not ok' : 'ok';
          $stats['status_code'] = 0;
        }
      }

      $this->output->echoln(sprintf('%s%s%s', substr($relativeFile, -min(67, strlen($relativeFile))), str_repeat('.', 70 - min(67, strlen($relativeFile))), $stats['status']));

      if (0 != $stats['status_code'])
      {
        $this->output->echoln(sprintf('    Test returned status %s', $stats['status_code']));
      }

      if ('ok' != $stats['status'])
      {
        $this->stats['failed_files'][] = $file;
      }

      if ($delta > 0)
      {
        $this->output->echoln(sprintf('    Looks like you planned %d tests but only ran %d.', $fileStats['plan'], $fileStats['total']));

        $this->stats['failed_tests'] += $delta;
        $this->stats['total'] += $delta;
      }
      else if ($delta < 0)
      {
        $this->output->echoln(sprintf('    Looks like you planned %s test but ran %s extra.', $fileStats['plan'], $fileStats['total'] - $fileStats['plan']));
      }

      if (false !== $fileStats && $fileStats['failed'])
      {
        $this->stats['failed_tests'] += count($fileStats['failed']);

        $this->output->echoln(sprintf("    Failed tests: %s", implode(', ', $fileStats['failed'])));
      }
    }

    if (count($this->stats['failed_files']))
    {
      $format = "%-30s  %4s  %5s  %5s  %s";
      $this->output->echoln(sprintf($format, 'Failed Test', 'Stat', 'Total', 'Fail', 'List of Failed'));
      $this->output->echoln("------------------------------------------------------------------");
      foreach ($this->stats['files'] as $file => $stat)
      {
        if (!in_array($file, $this->stats['failed_files']))
        {
          continue;
        }
        $relativeFile = $this->getRelativeFile($file);

        if (isset($stat['output'][0]))
        {
          $this->output->echoln(sprintf($format, substr($relativeFile, -min(30, strlen($relativeFile))), $stat['status_code'], count($stat['output'][0]['stats']['failed']) + count($stat['output'][0]['stats']['passed']), count($stat['output'][0]['stats']['failed']), implode(' ', $stat['output'][0]['stats']['failed'])));
        }
        else
        {
          $this->output->echoln(sprintf($format, substr($relativeFile, -min(30, strlen($relativeFile))), $stat['status_code'], '', '', ''));
        }
      }

      $this->output->redBar(sprintf('Failed %d/%d test scripts, %.2f%% okay. %d/%d subtests failed, %.2f%% okay.',
        $nbFailedFiles = count($this->stats['failed_files']),
        $nbFiles = count($this->files),
        ($nbFiles - $nbFailedFiles) * 100 / $nbFiles,
        $nbFailedTests = $this->stats['failed_tests'],
        $nbTests = $this->stats['total'],
        $nbTests > 0 ? ($nbTests - $nbFailedTests) * 100 / $nbTests : 0
      ));

      if ($this->options['verbose'])
      {
        foreach ($this->toArray() as $testSuite)
        {
          $first = true;
          foreach ($testSuite['stats']['failed'] as $testCase)
          {
            if (!isset($testSuite['tests'][$testCase]['file']))
            {
              continue;
            }

            if ($first)
            {
              $this->output->echoln('');
              $this->output->error($testSuite['file']);
              $first = false;
            }

            $this->output->comment(sprintf('  at %s line %s', $testSuite['tests'][$testCase]['file'], $testSuite['tests'][$testCase]['line']));
            $this->output->info('  '.$testSuite['tests'][$testCase]['message']);
            $this->output->echoln($testSuite['tests'][$testCase]['error'], null, false);
          }
        }
      }
    }
    else
    {
      $this->output->greenBar(' All tests successful.');
      $this->output->greenBar(sprintf(' Files=%d, Tests=%d', count($this->files), $this->stats['total']));
    }

    return $this->stats['failed_files'] ? false : true;
  }

  public function getFailedFiles()
  {
    return isset($this->stats['failed_files']) ? $this->stats['failed_files'] : array();
  }
}

class sfLimeCoverage extends sfLimeRegistration
{
  public $files = array();
  public $extension = '.php';
  public $baseDir = '';
  public $harness = null;
  public $verbose = false;
  protected $coverage = array();

  public function __construct($harness)
  {
    $this->harness = $harness;

    if (!function_exists('xdebug_start_code_coverage'))
    {
      throw new Exception('You must install and enable xdebug before using lime coverage.');
    }

    if (!ini_get('xdebug.extended_info'))
    {
      throw new Exception('You must set xdebug.extended_info to 1 in your php.ini to use lime coverage.');
    }
  }

  public function run()
  {
    if (!count($this->harness->files))
    {
      throw new Exception('You must register some test files before running coverage!');
    }

    if (!count($this->files))
    {
      throw new Exception('You must register some files to cover!');
    }

    $this->coverage = array();

    $this->process($this->harness->files);

    $this->output($this->files);
  }

  public function process($files)
  {
    if (!is_array($files))
    {
      $files = array($files);
    }

    $tmpFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'test.php';
    foreach ($files as $file)
    {
      $tmp = <<<EOF
<?php
xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
include('$file');
echo '<PHP_SER>'.serialize(xdebug_get_code_coverage()).'</PHP_SER>';
EOF;
      file_put_contents($tmpFile, $tmp);
      ob_start();
      // see http://trac.symfony-project.org/ticket/5437 for the explanation on the weird "cd" thing
      passthru(sprintf('cd & %s %s 2>&1', escapeshellarg($this->harness->executable), escapeshellarg($tmpFile)), $return);
      $retval = ob_get_clean();

      if (0 != $return) // test exited without success
      {
        // something may have gone wrong, we should warn the user so they know
        // it's a bug in their code and not symfony's

        $this->harness->output->echoln(sprintf('Warning: %s returned status %d, results may be inaccurate', $file, $return), 'ERROR');
      }

      if (false === $cov = @unserialize(substr($retval, strpos($retval, '<PHP_SER>') + 9, strpos($retval, '</PHP_SER>') - 9)))
      {
        if (0 == $return)
        {
          // failed to serialize, but PHP said it should of worked.
          // something is seriously wrong, so abort with exception
          throw new Exception(sprintf('Unable to unserialize coverage for file "%s"', $file));
        }
        else
        {
          // failed to serialize, but PHP warned us that this might have happened.
          // so we should ignore and move on
          continue; // continue foreach loop through $this->harness->files
        }
      }

      foreach ($cov as $file => $lines)
      {
        if (!isset($this->coverage[$file]))
        {
          $this->coverage[$file] = $lines;
          continue;
        }

        foreach ($lines as $line => $flag)
        {
          if ($flag == 1)
          {
            $this->coverage[$file][$line] = 1;
          }
        }
      }
    }

    if (file_exists($tmpFile))
    {
      unlink($tmpFile);
    }
  }

  public function output($files)
  {
    ksort($this->coverage);
    $totalPhpLines = 0;
    $totalCoveredLines = 0;
    foreach ($files as $file)
    {
      $file = realpath($file);
      $isCovered = isset($this->coverage[$file]);
      $cov = isset($this->coverage[$file]) ? $this->coverage[$file] : array();
      $coveredLines = array();
      $missingLines = array();

      foreach ($cov as $line => $flag)
      {
        switch ($flag)
        {
          case 1:
            $coveredLines[] = $line;
            break;
          case -1:
            $missingLines[] = $line;
            break;
        }
      }

      $totalLines = count($coveredLines) + count($missingLines);
      if (!$totalLines)
      {
        // probably means that the file is not covered at all!
        $totalLines = count($this->getPhpLines(file_get_contents($file)));
      }

      $output = $this->harness->output;
      $percent = $totalLines ? count($coveredLines) * 100 / $totalLines : 0;

      $totalPhpLines += $totalLines;
      $totalCoveredLines += count($coveredLines);

      $relativeFile = $this->getRelativeFile($file);
      $output->echoln(sprintf("%-70s %3.0f%%", substr($relativeFile, -min(70, strlen($relativeFile))), $percent), $percent == 100 ? 'INFO' : ($percent > 90 ? 'PARAMETER' : ($percent < 20 ? 'ERROR' : '')));
      if ($this->verbose && $isCovered && $percent != 100)
      {
        $output->comment(sprintf("missing: %s", $this->formatRange($missingLines)));
      }
    }

    $output->echoln(sprintf("TOTAL COVERAGE: %3.0f%%", $totalPhpLines ? $totalCoveredLines * 100 / $totalPhpLines : 0));
  }

  public static function getPhpLines($content)
  {
    if (is_readable($content))
    {
      $content = file_get_contents($content);
    }

    $tokens = token_get_all($content);
    $phpLines = array();
    $currentLine = 1;
    $inClass = false;
    $inFunction = false;
    $inFunctionDeclaration = false;
    $endOfCurrentExpr = true;
    $openBraces = 0;
    foreach ($tokens as $token)
    {
      if (is_string($token))
      {
        switch ($token)
        {
          case '=':
            if (false === $inClass || (false !== $inFunction && !$inFunctionDeclaration))
            {
              $phpLines[$currentLine] = true;
            }
            break;
          case '{':
            ++$openBraces;
            $inFunctionDeclaration = false;
            break;
          case ';':
            $inFunctionDeclaration = false;
            $endOfCurrentExpr = true;
            break;
          case '}':
            $endOfCurrentExpr = true;
            --$openBraces;
            if ($openBraces == $inClass)
            {
              $inClass = false;
            }
            if ($openBraces == $inFunction)
            {
              $inFunction = false;
            }
            break;
        }

        continue;
      }

      list($id, $text) = $token;

      switch ($id)
      {
        case T_CURLY_OPEN:
        case T_DOLLAR_OPEN_CURLY_BRACES:
          ++$openBraces;
          break;
        case T_WHITESPACE:
        case T_OPEN_TAG:
        case T_CLOSE_TAG:
          $endOfCurrentExpr = true;
          $currentLine += count(explode("\n", $text)) - 1;
          break;
        case T_COMMENT:
        case T_DOC_COMMENT:
          $currentLine += count(explode("\n", $text)) - 1;
          break;
        case T_CLASS:
          $inClass = $openBraces;
          break;
        case T_FUNCTION:
          $inFunction = $openBraces;
          $inFunctionDeclaration = true;
          break;
        case T_AND_EQUAL:
        case T_BREAK:
        case T_CASE:
        case T_CATCH:
        case T_CLONE:
        case T_CONCAT_EQUAL:
        case T_CONTINUE:
        case T_DEC:
        case T_DECLARE:
        case T_DEFAULT:
        case T_DIV_EQUAL:
        case T_DO:
        case T_ECHO:
        case T_ELSEIF:
        case T_EMPTY:
        case T_ENDDECLARE:
        case T_ENDFOR:
        case T_ENDFOREACH:
        case T_ENDIF:
        case T_ENDSWITCH:
        case T_ENDWHILE:
        case T_EVAL:
        case T_EXIT:
        case T_FOR:
        case T_FOREACH:
        case T_GLOBAL:
        case T_IF:
        case T_INC:
        case T_INCLUDE:
        case T_INCLUDE_ONCE:
        case T_INSTANCEOF:
        case T_ISSET:
        case T_IS_EQUAL:
        case T_IS_GREATER_OR_EQUAL:
        case T_IS_IDENTICAL:
        case T_IS_NOT_EQUAL:
        case T_IS_NOT_IDENTICAL:
        case T_IS_SMALLER_OR_EQUAL:
        case T_LIST:
        case T_LOGICAL_AND:
        case T_LOGICAL_OR:
        case T_LOGICAL_XOR:
        case T_MINUS_EQUAL:
        case T_MOD_EQUAL:
        case T_MUL_EQUAL:
        case T_NEW:
        case T_OBJECT_OPERATOR:
        case T_OR_EQUAL:
        case T_PLUS_EQUAL:
        case T_PRINT:
        case T_REQUIRE:
        case T_REQUIRE_ONCE:
        case T_RETURN:
        case T_SL:
        case T_SL_EQUAL:
        case T_SR:
        case T_SR_EQUAL:
        case T_SWITCH:
        case T_THROW:
        case T_TRY:
        case T_UNSET:
        case T_UNSET_CAST:
        case T_USE:
        case T_WHILE:
        case T_XOR_EQUAL:
          $phpLines[$currentLine] = true;
          $endOfCurrentExpr = false;
          break;
        default:
          if (false === $endOfCurrentExpr)
          {
            $phpLines[$currentLine] = true;
          }
      }
    }

    return $phpLines;
  }

  public function compute($content, $cov)
  {
    $phpLines = self::getPhpLines($content);

    // we remove from $cov non php lines
    foreach (array_diff_key($cov, $phpLines) as $line => $tmp)
    {
      unset($cov[$line]);
    }

    return array($cov, $phpLines);
  }

  public function formatRange($lines)
  {
    sort($lines);
    $formatted = '';
    $first = -1;
    $last = -1;
    foreach ($lines as $line)
    {
      if ($last + 1 != $line)
      {
        if ($first != -1)
        {
          $formatted .= $first == $last ? "$first " : "[$first - $last] ";
        }
        $first = $line;
        $last = $line;
      }
      else
      {
        $last = $line;
      }
    }
    if ($first != -1)
    {
      $formatted .= $first == $last ? "$first " : "[$first - $last] ";
    }

    return $formatted;
  }
}

class sfLimeRegistration
{
  public $files = array();
  public $extension = '.php';
  public $baseDir = '';

  public function register($filesOrDirectories)
  {
    foreach ((array) $filesOrDirectories as $fileOrDirectory)
    {
      if (is_file($fileOrDirectory))
      {
        $this->files[] = realpath($fileOrDirectory);
      }
      elseif (is_dir($fileOrDirectory))
      {
        $this->registerDir($fileOrDirectory);
      }
      else
      {
        throw new Exception(sprintf('The file or directory "%s" does not exist.', $fileOrDirectory));
      }
    }
  }

  public function registerGlob($glob)
  {
    if ($dirs = glob($glob))
    {
      foreach ($dirs as $file)
      {
        $this->files[] = realpath($file);
      }
    }
  }

  public function registerDir($directory)
  {
    if (!is_dir($directory))
    {
      throw new Exception(sprintf('The directory "%s" does not exist.', $directory));
    }

    $files = array();

    $currentDir = opendir($directory);
    while ($entry = readdir($currentDir))
    {
      if ($entry == '.' || $entry == '..') continue;

      if (is_dir($entry))
      {
        $this->registerDir($entry);
      }
      elseif (preg_match('#'.$this->extension.'$#', $entry))
      {
        $files[] = realpath($directory.DIRECTORY_SEPARATOR.$entry);
      }
    }

    $this->files = array_merge($this->files, $files);
  }

  protected function getRelativeFile($file)
  {
    return str_replace(DIRECTORY_SEPARATOR, '/', str_replace(array(realpath($this->baseDir).DIRECTORY_SEPARATOR, $this->extension), '', $file));
  }
}
