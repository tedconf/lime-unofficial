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
      'force_colors' => false,
      'output'       => null,
      'verbose'      => false,
    ), $options);

    $this->options['base_dir'] = realpath($this->options['base_dir']);

    $output = $this->options['output'] ? $this->options['output'] : $this->getDefaultOutput($this->options['force_colors']);

    $this->output = new LimeOutputInspectable($output);
  }

  protected function getDefaultOutput($forceColors = false)
  {
    if (in_array('--raw', $GLOBALS['argv']))
    {
      return new LimeOutputRaw();
    }
    else if (in_array('--xml', $GLOBALS['argv']))
    {
      return new LimeOutputXml();
    }
    else if (in_array('--array', $GLOBALS['argv']))
    {
      $serialize = in_array('--serialize', $GLOBALS['argv']);

      return new LimeOutputArray($serialize);
    }
    else
    {
      $colorizer = LimeColorizer::isSupported() || $forceColors ? new LimeColorizer() : null;

      return new LimeOutputConsoleSummary(new LimePrinter($colorizer), $this->options['base_dir']);
    }
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