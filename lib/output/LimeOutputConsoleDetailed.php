<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeOutputConsoleDetailed implements LimeOutputInterface
{
  protected
    $expected = null,
    $passed = 0,
    $actual = 0,
    $warnings = 0,
    $errors = 0,
    $printer = null;

  public function __construct(LimePrinter $printer)
  {
    $this->printer = $printer;
  }

  public function plan($amount, $file)
  {
    $this->expected = $amount;
    $this->printer->printLine('1..'.$amount);
  }

  public function pass($message, $file, $line)
  {
    $this->actual++;
    $this->passed++;

    if (empty($message))
    {
      $this->printer->printLine('ok '.$this->actual, LimePrinter::OK);
    }
    else
    {
      $this->printer->printText('ok '.$this->actual, LimePrinter::OK);
      $this->printer->printLine(' - '.$message);
    }
  }

  public function fail($message, $file, $line, $error = null)
  {
    $this->actual++;

    if (empty($message))
    {
      $this->printer->printLine('not ok '.$this->actual, LimePrinter::NOT_OK);
    }
    else
    {
      $this->printer->printText('not ok '.$this->actual, LimePrinter::NOT_OK);
      $this->printer->printLine(' - '.$message);
    }

    $this->printer->printLine(sprintf('#     Failed test (%s at line %s)', $file, $line), LimePrinter::COMMENT);

    if (!is_null($error))
    {
      foreach (explode("\n", $error) as $line)
      {
        $this->printer->printLine('#       '.$line, LimePrinter::COMMENT);
      }
    }
  }

  public function skip($message, $file, $line)
  {
    $this->actual++;
    $this->passed++;

    if (empty($message))
    {
      $this->printer->printLine('skip '.$this->actual, LimePrinter::SKIP);
    }
    else
    {
      $this->printer->printText('skip '.$this->actual, LimePrinter::SKIP);
      $this->printer->printLine(' - '.$message);
    }
  }

  public function warning($message, $file, $line)
  {
    $this->warnings++;

    $message .= sprintf("\n(in %s on line %s)", $file, $line);

    $this->printer->printLargeBox($message, LimePrinter::WARNING);
  }

  public function error($message, $file, $line)
  {
    $this->errors++;

    $message .= sprintf("\n(in %s on line %s)", $file, $line);

    $this->printer->printLargeBox($message, LimePrinter::ERROR);
  }

  public function comment($message)
  {
    $this->printer->printLine('# '.$message, LimePrinter::COMMENT);
  }

  public function flush()
  {
    if (is_null($this->expected))
    {
      $this->plan($this->actual, null);
    }

    if ($this->passed == $this->expected && $this->passed === $this->actual && $this->errors == 0)
    {
      if ($this->warnings > 0)
      {
        $this->printer->printBox(' Looks like you\'re nearly there.', LimePrinter::WARNING);
      }
      else
      {
        $this->printer->printBox(' Looks like everything went fine.', LimePrinter::HAPPY);
      }
    }
    else if ($this->passed != $this->actual)
    {
      $this->printer->printBox(sprintf(' Looks like you failed %s tests of %s.', $this->actual - $this->passed, $this->actual), LimePrinter::ERROR);
    }
    else if ($this->errors > 0)
    {
      $this->printer->printBox(' Looks like some errors occurred.', LimePrinter::ERROR);
    }

    if ($this->actual > $this->expected)
    {
      $this->printer->printBox(sprintf(' Looks like you planned %s tests but ran %s extra.', $this->expected, $this->actual - $this->expected), LimePrinter::ERROR);
    }
    else if ($this->actual < $this->expected)
    {
      $this->printer->printBox(sprintf(' Looks like you planned %s tests but only ran %s.', $this->expected, $this->actual), LimePrinter::ERROR);
    }
  }
}