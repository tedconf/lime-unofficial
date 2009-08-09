<?php

class LimeOutputFactory
{
  protected
    $options = array();

  public function __construct(array $options)
  {
    $this->options = array_merge(array(
      'serialize'     => false,
      'force_colors'  => false,
      'base_dir'      => null,
    ), $options);
  }

  public function create($name)
  {
    $colorizer = LimeColorizer::isSupported() || $this->options['force_colors'] ? new LimeColorizer() : null;
    $printer = new LimePrinter($colorizer);

    switch ($name)
    {
      case 'raw':
        return new LimeOutputRaw();
      case 'xml':
        return new LimeOutputXml();
      case 'array':
        return new LimeOutputArray($this->options['serialize']);
      case 'summary':
        return new LimeOutputConsoleSummary($printer, $this->options);
      case 'detail':
      default:
        return new LimeOutputConsoleDetailed($printer, $this->options);
    }
  }
}