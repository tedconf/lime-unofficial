<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeOutputRawConnector
{
  protected
    $error = false,
    $buffer = '',
    $output = null;

  public function __construct(LimeOutputInterface $output)
  {
    $this->output = $output;
  }

  public function connect($file, array $arguments = array())
  {
    if (!in_array('--raw', $arguments))
    {
      $arguments[] = '--raw';
    }

    $this->buffer = '';

    $shell = new LimeShell();
    $shell->executeCallback(array($this, 'unserializeLines'), $file, $arguments);

    if (!empty($this->buffer))
    {
      throw new RuntimeException(sprintf('Could not unserialize "%s"', $this->buffer));
    }
  }

  public function unserializeLines($lines)
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

        if ($method != 'flush')
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
  }

  public function failedUnserialize()
  {
    $this->error = true;
  }
}