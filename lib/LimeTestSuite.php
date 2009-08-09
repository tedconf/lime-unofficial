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
    $this->options = array(
      'base_dir'     => null,
      'executable'   => null,
      'output'       => 'summary',
      'force_colors' => false,
      'verbose'      => false,
      'serialize'    => false,
    );

    foreach (LimeShell::parseArguments($GLOBALS['argv']) as $argument => $value)
    {
      $this->options[str_replace('-', '_', $argument)] = $value;
    }

    $this->options = array_merge($this->options, $options);

    $this->options['base_dir'] = realpath($this->options['base_dir']);

    if (is_string($this->options['output']))
    {
      $factory = new LimeOutputFactory($this->options);

      $this->options['output'] = $factory->create($this->options['output']);
    }

    $this->output = new LimeOutputInspectable($this->options['output']);
  }

  public function run()
  {
    if (!count($this->files))
    {
      throw new Exception('You must register some test files before running them!');
    }

    // sort the files to be able to predict the order
    sort($this->files);

    $connector = new LimeOutputPipe($this->output, array('start', 'flush'));

    foreach ($this->files as $file)
    {
      // start the file explicitly in case the file contains syntax errors
      $this->output->start($file);
      $connector->connect($file);
    }

    $this->output->flush();

    $failed = $this->output->getFailed();
    $errors = $this->output->getErrors();
    $warnings = $this->output->getWarnings();
    $skipped = $this->output->getSkipped();

    return 0 == ($failed + $errors + $warnings + $skipped);
  }
}