<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeTestAnalyzer
{
  protected
    $suppressedMethods  = array(),
    $output             = null,
    $shell              = null,
    $file               = null,
    $handle             = null,
    $done               = true,
    $parser             = null;

  public function __construct(LimeOutputInterface $output, array $suppressedMethods = array())
  {
    $this->suppressedMethods = $suppressedMethods;
    $this->output = $output;
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
    $this->done = false;
    $this->handle = $this->shell->spawn($file, $arguments);
    $this->parser = null;
  }

  public function proceed()
  {
    $data = fread($this->handle, 1024);

    if (is_null($this->parser))
    {
      if (substr($data, 0, 5) == "\0raw\0")
      {
        $this->parser = new LimeParserRaw($this->output, $this->suppressedMethods);
        $data = substr($data, 5);
      }
      else
      {
        $this->parser = new LimeParserTap($this->output);
      }
    }

    $this->parser->parse($data);

    if (feof($this->handle))
    {
      if (!$this->parser->done())
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
}