<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * LimeAutoloader is an autoloader for the lime test framework classes.
 *
 * Use the method register() to activate autoloading for all classes of this
 * component.
 *
 * <code>
 * include 'path/to/LimeAutoloader.php';
 * LimeAutoloader::register();
 * </code>
 *
 * Bundled with this component comes a backwards compatibility layer that
 * offers class and method signatures of lime 1.0 (lime_test, lime_harness etc.).
 * To activate this layer, call the method LimeAutoloader::enableLegacyMode()
 * anytime before using any of the old class names in your code.
 *
 * <code>
 * include 'path/to/LimeAutoloader.php';
 * LimeAutoloader::register();
 * LimeAutoloader::enableLegacyMode();
 * </code>
 *
 * @package    symfony
 * @subpackage lime
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class LimeAutoloader
{
  static protected
    $isLegacyMode = false,
    $isRegistered = false;

  /**
   * Enables a backwards compatibility layer to allow use of old class names
   * such as lime_test, lime_output etc.
   */
  static public function enableLegacyMode()
  {
    self::$isLegacyMode = true;
  }

  /**
   * Registers LimeAutoloader as an SPL autoloader.
   */
  static public function register()
  {
    if (!self::$isRegistered)
    {
      ini_set('unserialize_callback_func', 'spl_autoload_call');
      spl_autoload_register(array(new self, 'autoload'));

      self::$isRegistered = true;
    }
  }

  /**
   * Handles autoloading of classes.
   *
   * @param  string  $class  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  public function autoload($class)
  {
    // backwards compatibility
    if (0 === strpos($class, 'lime_') && self::$isLegacyMode)
    {
      require dirname(__FILE__).'/lime.php';

      return true;
    }

    if (0 === strpos($class, 'Lime'))
    {
      $dir = '/';

      if (0 === strpos($class, 'LimeExpectation'))
      {
        $dir .= 'expectation/';
      }
      else if (0 === strpos($class, 'LimeLexer'))
      {
        $dir .= 'lexer/';
      }
      else if (0 === strpos($class, 'LimeMock'))
      {
        $dir .= 'mock/';
      }

      require dirname(__FILE__).$dir.$class.'.php';

      return true;
    }

    return false;
  }
}