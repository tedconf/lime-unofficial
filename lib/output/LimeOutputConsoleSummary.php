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
    $printer = null,
    $baseDir = null,
    $startTime = 0,
    $file = null,
    $actualFiles = 0,
    $failedFiles = 0,
    $actualTests = 0,
    $failedTests = 0,
    $expected = 0,
    $passed = 0,
    $failed = 0,
    $errors = 0,
    $warnings = 0;

  public function __construct(LimePrinter $printer, $baseDir = null)
  {
    $this->printer = $printer;
    $this->baseDir = $baseDir;
    $this->startTime = time();
  }

  public function start($file)
  {
    $this->close();

    $this->file = $file;
    $this->expected = 0;
    $this->passed = 0;
    $this->failed = 0;
    $this->errors = 0;
    $this->warnings = 0;
  }

  protected function close()
  {
    if (!is_null($this->file))
    {
      $this->actualFiles++;
      $this->actualTests += $this->passed + $this->failed;
      $this->failedTests += $this->failed;

      $actual = $this->passed+$this->failed;
      $incomplete = ($this->expected > 0 && $actual != $this->expected);

      $this->printer->printText(str_pad($this->getTruncatedFile(), 73, '.'));

      if ($this->errors || $this->failed || $incomplete)
      {
        $this->failedFiles++;
        $this->printer->printLine('not ok', LimePrinter::NOT_OK);
      }
      else if ($this->warnings)
      {
        $this->printer->printLine('warning', LimePrinter::WARNING);
      }
      else
      {
        $this->printer->printLine('ok', LimePrinter::OK);
      }

      if ($this->errors || $this->warnings || $this->failed)
      {
        $this->printer->printText('    ');
        $this->printer->printText('Passed: '.$this->passed);
        $this->printer->printText(str_repeat(' ', 6 - strlen($this->passed)));
        $this->printer->printText('Failed: '.$this->failed, $this->failed > 0 ? LimePrinter::NOT_OK : null);
        $this->printer->printText(str_repeat(' ', 6 - strlen($this->failed)));
        $this->printer->printText('Warnings: '.$this->warnings, $this->warnings > 0 ? LimePrinter::WARNING : null);
        $this->printer->printText(str_repeat(' ', 6 - strlen($this->warnings)));
        $this->printer->printLine('Errors: '.$this->errors, $this->errors > 0 ? LimePrinter::NOT_OK : null);
      }

      if ($this->errors || $this->warnings || $this->failed || $incomplete)
      {
        $messages = LimeOutputConsoleDetailed::getMessages($actual,
            $this->expected, $this->passed, $this->errors, $this->warnings);

        foreach ($messages as $message)
        {
          list ($message, $style) = $message;
          $this->printer->printLine('    '.$message);
        }
      }
    }
  }

  public function plan($amount)
  {
    $this->expected = $amount;
  }

  public function pass($message, $file, $line)
  {
    $this->passed++;

    $this->update();
  }

  public function fail($message, $file, $line, $error = null)
  {
    $this->failed++;

    $this->update();
  }

  public function skip($message, $file, $line) {}

  public function todo($message, $file, $line)
  {
  }

  public function warning($message, $file, $line)
  {
    $this->warnings++;
  }

  public function error(Exception $exception)
  {
    $this->errors++;
  }

  public function comment($message) {}

  public function flush()
  {
    $this->close();

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
      $stats = sprintf(' Files=%d, Tests=%d, Time=%02d:%02d',
          $this->actualFiles, $this->actualTests, round($time/60), $time%60);

      $this->printer->printBox(' All tests successful.', LimePrinter::HAPPY);
      $this->printer->printBox($stats, LimePrinter::HAPPY);
    }
  }

  protected function update()
  {
    if ($this->errors || $this->failed)
    {
      $style = LimePrinter::NOT_OK;
    }
    else if ($this->warnings)
    {
      $style = LimePrinter::WARNING;
    }
    else
    {
      $style = LimePrinter::OK;
    }

    $tests = $this->passed + $this->failed;

    $this->printer->printText(str_pad($this->getTruncatedFile(), 73, '.'));
    $this->printer->printText($tests."\r", $style);
  }

  protected function getTruncatedFile()
  {
    if (!is_null($this->baseDir))
    {
      return str_replace($this->baseDir, '', $this->file);
    }
    else
    {
      return $this->file;
    }

  }
}