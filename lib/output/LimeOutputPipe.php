<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeOutputPipe
{
  protected
    $suppressedMethods = array(),
    $error = false,
    $buffer = '',
    $output = null,
    $shell = null,
    $file = null,
    $handle = null,
    $done = true;

  public function __construct(LimeOutputInterface $output, array $suppressedMethods = array())
  {
    $this->output = $output;
    $this->suppressedMethods = $suppressedMethods;
    $this->shell = new LimeShell();
  }

  public function getConnectedFile()
  {
    return $this->file;
  }

  public function connect($file, array $arguments = array())
  {
    $arguments['output'] = 'raw';

    $this->file = $file;
    $this->buffer = '';
    $this->done = false;
    $this->handle = $this->shell->spawn($file, $arguments);
  }

  public function proceed()
  {
    $this->unserializeLines(fread($this->handle, 2048));

    if (feof($this->handle))
    {
      if (!empty($this->buffer))
      {
        $this->output->warning("Could not parse test output. Make sure you don't echo any additional data.", $this->file, 1);
      }

      $this->done = true;
    }
  }

  public function done()
  {
    return $this->done;
  }

  protected function unserializeLines($lines)
  {
    $this->buffer .= $lines;

    $lines = explode("\n", $this->buffer);

    while ($line = array_shift($lines))
    {
      if (!empty($line))
      {
        $this->error = false;

        set_error_handler(array($this, 'failedUnserialize'));
        list($method, $arguments) = unserialize($line);
        restore_error_handler();

        if ($this->error)
        {
          // prepend the line again, maybe we can unserialize later
          array_unshift($lines, $line);
          break;
        }

        if (!in_array($method, $this->suppressedMethods))
        {
          foreach ($arguments as &$argument)
          {
            if (is_string($argument))
            {
              $argument = stripcslashes($argument);
            }
          }
          call_user_func_array(array($this->output, $method), $arguments);
        }
      }
    }

    $this->buffer = implode("\n", $lines);

    while (!empty($this->buffer))
    {
      if (preg_match('/^\s*([\w\s]+)(: .+) in (.+) on line (\d+)/', $this->buffer, $matches))
      {
        $this->buffer = trim(substr($this->buffer, strlen($matches[0])));

        if ($matches[1] == 'Warning')
        {
          $this->output->warning($matches[1].$matches[2], $matches[3], $matches[4]);
        }
        else
        {
          $this->output->error(new LimeError($matches[1].$matches[2], $matches[3], $matches[4]));
        }

        // consume Xdebug call stack
        while (preg_match('/^(Call Stack:|\d\.\d+\s+\d+\s+\d+\.\s+.+:\d+)/', $this->buffer, $matches))
        {
          $this->buffer = trim(substr($this->buffer, strlen($matches[0])));
        }
      }
      else
      {
        break;
      }
    }
  }

  public function failedUnserialize()
  {
    $this->error = true;
  }
}