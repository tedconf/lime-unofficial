<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeOutputConsoleSummary implements LimeOutputInterface
{
  protected
    $printer        = null,
    $options        = array(),
    $startTime      = 0,
    $file           = null,
    $actualFiles    = 0,
    $failedFiles    = 0,
    $actualTests    = 0,
    $failedTests    = 0,
    $expected       = array(),
    $passed         = array(),
    $failed         = array(),
    $errors         = array(),
    $warnings       = array(),
    $line           = array();

  public function __construct(LimePrinter $printer, array $options = array())
  {
    $this->printer = $printer;
    $this->startTime = time();
    $this->options = array_merge(array(
      'base_dir'  => null,
      'processes' => 1,
    ), $options);
  }

  public function supportsThreading()
  {
    return true;
  }

  public function focus($file)
  {
    $this->file = $file;

    if (!array_key_exists($file, $this->line))
    {
      $this->line[$file] = count($this->line);
      $this->expected[$file] = 0;
      $this->passed[$file] = 0;
      $this->failed[$file] = 0;
      $this->errors[$file] = 0;
      $this->warnings[$file] = 0;
    }
  }

  public function close()
  {
    if (!is_null($this->file))
    {
      $this->actualFiles++;
      $this->actualTests += $this->getActual();
      $this->failedTests += $this->getFailed();

      $path = $this->getTruncatedFile();

      if (strlen($path) > 71)
      {
        $path = substr($path, -71);
      }

      $this->printer->printText(str_pad($path, 73, '.'));

      $incomplete = ($this->getExpected() > 0 && $this->getActual() != $this->getExpected());

      if ($this->getErrors() || $this->getFailed() || $incomplete)
      {
        $this->failedFiles++;
        $this->printer->printLine("not ok", LimePrinter::NOT_OK);
      }
      else if ($this->getWarnings())
      {
        $this->printer->printLine("warning", LimePrinter::WARNING);
      }
      else
      {
        $this->printer->printLine("ok", LimePrinter::OK);
      }

      if ($this->getErrors() || $this->getWarnings() || $this->getFailed())
      {
        $this->printer->printText('    ');
        $this->printer->printText('Passed: '.$this->getPassed());
        $this->printer->printText(str_repeat(' ', 6 - strlen($this->getPassed())));
        $this->printer->printText('Failed: '.$this->getFailed(), $this->getFailed() > 0 ? LimePrinter::NOT_OK : null);
        $this->printer->printText(str_repeat(' ', 6 - strlen($this->getFailed())));
        $this->printer->printText('Warnings: '.$this->getWarnings(), $this->getWarnings() > 0 ? LimePrinter::WARNING : null);
        $this->printer->printText(str_repeat(' ', 6 - strlen($this->getWarnings())));
        $this->printer->printLine('Errors: '.$this->getErrors(), $this->getErrors() > 0 ? LimePrinter::NOT_OK : null);
      }

      if ($this->getErrors() || $this->getWarnings() || $this->getFailed() || $incomplete)
      {
        $messages = LimeOutputTap::getMessages($this->getActual(),
            $this->getExpected(), $this->getPassed(), $this->getErrors(), $this->getWarnings());

        foreach ($messages as $message)
        {
          list ($message, $style) = $message;
          $this->printer->printLine('    '.$message);
        }
      }
    }
  }

  protected function getExpected()
  {
    return $this->expected[$this->file];
  }

  protected function getActual()
  {
    return $this->getPassed() + $this->getFailed();
  }

  protected function getPassed()
  {
    return $this->passed[$this->file];
  }

  protected function getFailed()
  {
    return $this->failed[$this->file];
  }

  protected function getErrors()
  {
    return $this->errors[$this->file];
  }

  protected function getWarnings()
  {
    return $this->warnings[$this->file];
  }

  protected function setCursor()
  {
    for ($i = count($this->line); $i > $this->line[$this->file]; --$i)
    {
      $this->printer->previousLine();
    }
  }

  protected function resetCursor()
  {
    for ($i = $this->line[$this->file]; $i < count($this->line); ++$i)
    {
      $this->printer->nextLine();
    }
  }

  public function plan($amount)
  {
    $this->expected[$this->file] = $amount;
  }

  public function pass($message, $file, $line)
  {
    $this->passed[$this->file]++;
  }

  public function fail($message, $file, $line, $error = null)
  {
    $this->failed[$this->file]++;
  }

  public function skip($message, $file, $line) {}

  public function todo($message, $file, $line)
  {
  }

  public function warning($message, $file, $line)
  {
    $this->warnings[$this->file]++;
  }

  public function error(Exception $exception)
  {
    $this->errors[$this->file]++;
  }

  public function comment($message) {}

  public function flush()
  {
    if ($this->failedFiles > 0)
    {
      $stats = sprintf(' Failed %d/%d test scripts, %.2f%% okay. %d/%d subtests failed, %.2f%% okay.',
          $this->failedFiles, $this->actualFiles, 100 - 100*$this->failedFiles/$this->actualFiles,
          $this->failedTests, $this->actualTests, 100 - 100*$this->failedTests/$this->actualTests);

      $this->printer->printBox($stats, LimePrinter::NOT_OK);
    }
    else
    {
      $time = max(1, time() - $this->startTime);
      $stats = sprintf(' Files=%d, Tests=%d, Time=%02d:%02d, Processes=%d',
          $this->actualFiles, $this->actualTests, round($time/60), $time%60, $this->options['processes']);

      $this->printer->printBox(' All tests successful.', LimePrinter::HAPPY);
      $this->printer->printBox($stats, LimePrinter::HAPPY);
    }
  }

  protected function getTruncatedFile()
  {
    if (!is_null($this->options))
    {
      return str_replace($this->options['base_dir'], '', $this->file);
    }
    else
    {
      return $this->file;
    }
  }
}