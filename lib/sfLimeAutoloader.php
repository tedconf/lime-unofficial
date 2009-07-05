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
 * sfLimeAutoloader is an autoloader for the service container classes.
 *
 * @package    symfony
 * @subpackage lime
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfLimeAutoloader
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
   * Registers sfLimeAutoloader as an SPL autoloader.
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
      require dirname(__FILE__).'/legacy.php';

      return true;
    }

    if (0 === strpos($class, 'sfLime'))
    {
      require dirname(__FILE__).'/'.$class.'.php';

      return true;
    }

    return false;
  }
}