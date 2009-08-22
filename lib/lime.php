<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require_once dirname(__FILE__).'/LimeAutoloader.php';

LimeAutoloader::enableLegacyMode();
LimeAutoloader::register();

class lime_test extends LimeTest
{
  public function __construct($plan = null, $options = array())
  {
    // for BC
    if (!is_array($options))
    {
      $options = array(); // drop the old output because it is not compatible with LimeTest
    }

    parent::__construct($plan, $options);
  }

  static public function to_array()
  {
    return self::toArray();
  }

  static public function to_xml($results = null)
  {
    return self::toXml($results);
  }

  public function cmp_ok($exp1, $op, $exp2, $message = '')
  {
    return $this->compare($exp1, $op, $exp2, $message);
  }

  public function can_ok($object, $methods, $message = '')
  {
    return $this->hasMethod($object, $methods, $message);
  }

  public function isa_ok($var, $class, $message = '')
  {
    return $this->isa($var, $class, $message);
  }

  public function is_deeply($exp1, $exp2, $message = '')
  {
    return $this->isDeeply($exp1, $exp2, $message);
  }

  public function include_ok($file, $message = '')
  {
    return $this->includeOk($file, $message);
  }

  public function error($message)
  {
    list($file, $line) = LimeTrace::findCaller('lime');

    $this->output->error(new LimeError($message, $file, $line));
  }
}

class lime_output extends LimeOutput
{
  public function green_bar($message)
  {
    return $this->greenBar($message);
  }

  public function red_bar($message)
  {
    return $this->redBar($message);
  }
}

class lime_output_color extends LimeOutput
{
}

class lime_colorizer extends LimeColorizer
{
  protected static
    $instances    = array(),
    $staticStyles = array();

  public function __construct()
  {
    self::$instances[] = $this;
    $this->styles = self::$staticStyles;
  }

  public static function style($name, $options = array())
  {
    foreach (self::$instances as $instance)
    {
      $instance->setStyle($name, $options);
    }
    self::$staticStyles[$name] = $options;
  }
}

class lime_harness extends LimeTestSuite
{
  public function __construct($options = array())
  {
    // for BC
    if (!is_array($options))
    {
      $options = array(); // drop the old output because it is not compatible with LimeTest
    }
    else if (array_key_exists('php_cli', $options))
    {
      $options['executable'] = $options['php_cli'];
      unset($options['php_cli']);
    }

    parent::__construct($options);
  }

  public function to_array()
  {
    return $this->toArray();
  }

  public function to_xml()
  {
    return $this->toXml();
  }

  public function get_failed_files()
  {
    return $this->output->getFailedFiles();
  }
}

class lime_coverage extends LimeCoverage
{
  public static function get_php_lines($content)
  {
    return self::getPhpLines($content);
  }

  public function format_range($lines)
  {
    return $this->formatRange($lines);
  }
}

class lime_registration extends LimeRegistration
{
  public function register_glob($glob)
  {
    return $this->registerGlob($glob);
  }

  public function register_dir($directory)
  {
    return $this->registerDir($directory);
  }
}
