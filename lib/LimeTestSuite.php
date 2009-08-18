<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeTestSuite extends LimeRegistration
{
  protected
    $options    = array(),
    $executable = null,
    $output     = null;

  public function __construct(array $options = array())
  {
    $this->options = array_merge(array(
      'base_dir'     => null,
      'executable'   => null,
      'output'       => 'summary',
      'force_colors' => false,
      'verbose'      => false,
      'serialize'    => false,
      'processes'    => 1,
    ), $options);

    foreach (LimeShell::parseArguments($GLOBALS['argv']) as $argument => $value)
    {
      $this->options[str_replace('-', '_', $argument)] = $value;
    }

    $this->options['base_dir'] = realpath($this->options['base_dir']);

    if (is_string($this->options['output']))
    {
      $factory = new LimeOutputFactory($this->options);

      $type = $this->options['output'];
      $output = $factory->create($type);
    }
    else
    {
      $output = $this->options['output'];
      $type = get_class($output);
    }

    if ($this->options['processes'] > 1 && !$output->supportsThreading())
    {
      throw new LogicException(sprintf('The output "%s" does not support threading', $type));
    }

    $this->output = new LimeOutputInspectable($output);
  }

  public function run()
  {
    if (!count($this->files))
    {
      throw new Exception('You must register some test files before running them!');
    }

    // sort the files to be able to predict the order
    sort($this->files);
    reset($this->files);

    $connectors = array();

    for ($i = 0; $i < $this->options['processes']; ++$i)
    {
      $connectors[] = new LimeOutputPipe($this->output, array('focus', 'flush'));
    }

    do
    {
      $done = true;

      foreach ($connectors as $connector)
      {
        if ($connector->done() && !is_null(key($this->files)))
        {
          // start the file explicitly in case the file contains syntax errors
          $this->output->focus(current($this->files));
          $connector->connect(current($this->files));

          next($this->files);
        }
        else if (!$connector->done())
        {
          $this->output->focus($connector->getConnectedFile());
        }

        if (!$connector->done())
        {
          $connector->proceed();
          $done = false;
        }
      }
    }
    while (!$done);

    $this->output->flush();

    $failed = $this->output->getFailed();
    $errors = $this->output->getErrors();
    $warnings = $this->output->getWarnings();
    $skipped = $this->output->getSkipped();

    return 0 == ($failed + $errors + $warnings + $skipped);
  }
}