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
    $baseDir = null,
    $expected = null,
    $passed = 0,
    $actual = 0,
    $warnings = 0,
    $errors = 0,
    $printer = null;

  public function __construct(LimePrinter $printer, $baseDir = null)
  {
    $this->printer = $printer;
    $this->baseDir = $baseDir;
  }

  public function start($file)
  {
    if (!is_null($this->baseDir))
    {
      $file = str_replace($this->baseDir, '', $file);
    }

    $this->printer->printLine($file, LimePrinter::INFO);
  }

  public function plan($amount)
  {
    $this->expected += $amount;
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

  public function info($message)
  {
    $this->printer->printLine('# '.$message, LimePrinter::INFO);
  }

  public function comment($message)
  {
    $this->printer->printLine('# '.$message, LimePrinter::COMMENT);
  }

  public static function getMessages($actual, $expected, $passed, $errors, $warnings)
  {
    $messages = array();

    if ($passed == $expected && $passed === $actual && $errors == 0)
    {
      if ($warnings > 0)
      {
        $messages[] = array('Looks like you\'re nearly there.', LimePrinter::WARNING);
      }
      else
      {
        $messages[] = array('Looks like everything went fine.', LimePrinter::HAPPY);
      }
    }
    else if ($passed != $actual)
    {
      $messages[] = array(sprintf('Looks like you failed %s tests of %s.', $actual - $passed, $actual), LimePrinter::ERROR);
    }
    else if ($errors > 0)
    {
      $messages[] = array('Looks like some errors occurred.', LimePrinter::ERROR);
    }

    if ($actual > $expected)
    {
      $messages[] = array(sprintf('Looks like you planned %s tests but ran %s extra.', $expected, $actual - $expected), LimePrinter::ERROR);
    }
    else if ($actual < $expected)
    {
      $messages[] = array(sprintf('Looks like you planned %s tests but only ran %s.', $expected, $actual), LimePrinter::ERROR);
    }

    return $messages;
  }

  public function flush()
  {
    if (is_null($this->expected))
    {
      $this->plan($this->actual, null);
    }

    $messages = self::getMessages($this->actual, $this->expected, $this->passed, $this->errors, $this->warnings);

    foreach ($messages as $message)
    {
      list ($message, $style) = $message;

      $this->printer->printBox(' '.$message, $style);
    }
  }
}