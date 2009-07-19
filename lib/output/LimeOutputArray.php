<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeOutputArray implements LimeOutputInterface
{
  protected
    $results = array();

  public function plan($amount, $file)
  {
    $results =& $this->getResults($file);

    $results['stats']['plan'] = $amount;
  }

  public function pass($message, $file, $line)
  {
    $results =& $this->getResults($file);

    $results['stats']['total']++;
    $results['stats']['passed'][] = $this->addTest(true, $line, $file, $message);
  }

  public function fail($message, $file, $line, $actual, $expected)
  {
    $results =& $this->getResults($file);

    $index = $this->addTest(false, $line, $file, $message);

    $results['stats']['total']++;
    $results['stats']['failed'][] = $index;
    $results['tests'][$index]['error'] = sprintf("got: %s\nexpected: %s", var_export($actual, true), var_export($expected, true));
  }

  public function skip($message, $file, $line)
  {
    $results =& $this->getResults($file);

    $results['stats']['total']++;
    $results['stats']['skipped'][] = $this->addTest(true, $line, $file, $message);
  }

  public function warning($message, $file, $line)
  {
  }

  public function error($message, $file, $line)
  {
  }

  public function comment($message)
  {
  }

  public function toArray()
  {
    return $this->results;
  }

  protected function &getResults($file)
  {
    foreach ($this->results as $key => &$fileResults)
    {
      if ($fileResults['file'] == $file)
      {
        return $fileResults;
      }
    }

    $newResults = array(
      'file' => $file,
      'tests' => array(),
      'stats' => array(
        'plan' => 0,
        'total' => 0,
        'failed' => array(),
        'passed' => array(),
        'skipped' => array(),
      ),
    );

    $this->results[] =& $newResults;

    return $newResults;
  }

  protected function addTest($status, $line, $file, $message)
  {
    $results =& $this->getResults($file);
    $index = count($results['tests']) + 1;

    $results['tests'][$index] = array(
      'line' => $line,
      'file' => $file,
      'message' => $message,
      'status' => $status,
    );

    return $index;
  }
}