<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require_once dirname(__FILE__).'/sfLimeAutoloader.php';

sfLimeAutoloader::enableLegacyMode();
sfLimeAutoloader::register();

class lime_test extends sfLimeTest
{
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
}

class lime_output extends sfLimeOutput
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

class lime_output_color extends sfLimeOutputColor
{
}

class lime_colorizer extends sfLimeColorizer
{
}

class lime_harness extends sfLimeHarness
{
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
    return $this->getFailedFiles();
  }
}

class lime_coverage extends sfLimeCoverage
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

class lime_registration extends sfLimeRegistration
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
