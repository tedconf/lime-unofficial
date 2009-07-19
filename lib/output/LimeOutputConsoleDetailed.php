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
    $expected = 0,
    $actual = 0,
    $printer = null;

  public function __construct(LimePrinter $printer)
  {
    $this->printer = $printer;
  }

  public function __destruct()
  {
    if ($this->actual > $this->expected)
    {
      $this->printer->printBox(sprintf(' Looks like you planned %s tests but ran %s extra.', $this->expected, $this->actual-$this->expected), LimePrinter::ERROR);
    }
    else if ($this->actual < $this->expected)
    {
      $this->printer->printBox(sprintf(' Looks like you planned %s tests but only ran %s.', $this->expected, $this->actual), LimePrinter::ERROR);
    }
    else
    {
      $this->printer->printBox(' Looks like everything went fine.', LimePrinter::HAPPY);
    }
  }

  public function plan($amount, $file)
  {
    $this->expected = $amount;

    $this->printer->printLine('1..'.$amount);
  }

  public function pass($message, $file, $line)
  {
    $this->actual++;

    $this->printer->printText('ok '.$this->actual, LimePrinter::OK);
    $this->printer->printLine(' - '.$message);
  }

  public function fail($message, $file, $line, $error)
  {
    $this->actual++;

    $this->printer->printText('not ok '.$this->actual, LimePrinter::NOT_OK);
    $this->printer->printLine(' - '.$message);
    $this->printer->printLine(sprintf('#     Failed test (%s at line %s)', $file, $line), LimePrinter::COMMENT);

    foreach (explode("\n", $error) as $line)
    {
      $this->printer->printLine('#       '.$line, LimePrinter::COMMENT);
    }
  }

  public function skip($message, $file, $line)
  {
    $this->actual++;

    $this->printer->printText('skip '.$this->actual, LimePrinter::SKIP);
    $this->printer->printLine(' - '.$message);
  }

  public function warning($message, $file, $line)
  {
    $this->printer->printBox(' '.$message, LimePrinter::WARNING);
  }

  public function error($message, $file, $line)
  {
    $this->printer->printBox(' '.$message, LimePrinter::ERROR);
  }

  public function comment($message)
  {
    $this->printer->printLine('# '.$message, LimePrinter::COMMENT);
  }
}