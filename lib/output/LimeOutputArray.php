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
    $serialize = false,
    $results = array(),
    $currentResults = null;

  public function __construct($serialize = false)
  {
    $this->serialize = $serialize;
  }

  public function start($file)
  {
    $this->currentResults =& $this->getResults($file);
  }

  public function plan($amount)
  {
    $this->currentResults['stats']['plan'] = $amount;
  }

  public function pass($message, $file, $line)
  {
    $this->currentResults['stats']['total']++;
    $this->currentResults['stats']['passed'][] = $this->addTest(true, $line, $file, $message);
  }

  public function fail($message, $file, $line, $error = null)
  {
    $index = $this->addTest(false, $line, $file, $message);

    $this->currentResults['stats']['total']++;
    $this->currentResults['stats']['failed'][] = $index;

    if (!is_null($error))
    {
      $this->currentResults['tests'][$index]['error'] = $error;
    }
  }

  public function skip($message, $file, $line)
  {
    $this->currentResults['stats']['total']++;
    $this->currentResults['stats']['skipped'][] = $this->addTest(true, $line, $file, $message);
  }

  public function warning($message, $file, $line)
  {
  }

  public function error($message, $file, $line)
  {
  }

  public function info($message)
  {
  }

  public function comment($message)
  {
  }

  public function flush()
  {
    if ($this->serialize)
    {
      print serialize($this->results);
    }
    else
    {
      var_export($this->results);
    }
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
    $index = count($this->currentResults['tests']) + 1;

    $this->currentResults['tests'][$index] = array(
      'line' => $line,
      'file' => $file,
      'message' => $message,
      'status' => $status,
    );

    return $index;
  }
}