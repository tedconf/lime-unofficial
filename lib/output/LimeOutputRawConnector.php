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
    $output = null;

  public function __construct(LimeOutputInterface $output)
  {
    $this->output = $output;
  }

  public function connect($file, array $arguments = array())
  {
    $shell = new LimeShell();
    $shell->executeCallback(array($this, 'call'), $file, $arguments);
  }

  public function call($lines)
  {
    foreach (explode("\n", $lines) as $methodAndArguments)
    {
      if (!empty($methodAndArguments))
      {
        $this->error = false;

        set_error_handler(array($this, 'failedUnserialize'));
        list($method, $arguments) = unserialize($methodAndArguments);
        restore_error_handler();

        if ($this->error)
        {
          throw new RuntimeException(sprintf('Could not unserialize "%s"', $methodAndArguments));
        }

        if ($method != 'flush')
        {
          call_user_func_array(array($this->output, $method), $arguments);
        }
      }
    }
  }

  public function failedUnserialize()
  {
    $this->error = true;
  }
}