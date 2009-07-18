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
 * Extends lime_test to support annotations in test files.
 *
 * With this extension of lime_test, you can write very simple test files that
 * support more features than regular lime, such as code executed before
 * or after each test, code executed before or after the whole test suite
 * or expected exceptions.
 *
 * A test file can be written like this with LimeTest:
 *
 * <code>
 * <?php
 *
 * include dirname(__FILE__).'/../bootstrap/unit.php';
 *
 * $t = new LimeTest(2, new lime_output_color());
 *
 * // @Before
 * $r = new Record();
 *
 * // @Test
 * $r->setValue('Bumblebee');
 * $t->is($r->getValue(), 'Bumblebee', 'The setter works');
 *
 * // @Test
 * $t->is($r->getValue(), 'Foobar', 'The value is "Foobar" by default');
 * </code>
 *
 * The available annotations are:
 *
 *   * @BeforeAll  Executed before the whole test suite
 *   * @Before     Executed before each test
 *   * @After      Executed after each test
 *   * @AfterAll   Executed after the whole test suite
 *   * @Test       A test case
 *
 * You can add comments to the annotations that will be printed in the console:
 *
 * <code>
 * // @Test: The record supports setValue()
 * $r->setValue('Bumblebee')
 * // etc.
 * </code>
 *
 * You can also automatically test that certain exceptions are thrown from
 * within a test. To do that, you must call the method ->expect() on the
 * LimeTest object '''before''' executing the test that should throw
 * an exception.
 *
 * <code>
 * // @Test
 * $r->expect('RuntimeException');
 * throw new RuntimeException();
 *
 * // results in a passed test
 * </code>
 *
 * @package    lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class LimeAnnotationSupport
{
  protected static
    $annotations  = array('Test', 'Before', 'After', 'BeforeAll', 'AfterAll'),
    $enabled      = false,
    $path         = null,
    $test         = null,
    $testRunner   = null,
    $lexer        = null;

  /**
   * Enables annotation support in a script file.
   */
  public static function enable()
  {
    // make sure that annotations are not replaced twice at the same time
    if (!self::$enabled)
    {
      self::$enabled = true;

      self::initialize(self::getScriptPath(), new LimeTestRunner());
      self::execute();

      exit;
    }
  }

  /**
   * Returns the file path of the executed test script
   *
   * @return string  The file path
   */
  protected static function getScriptPath()
  {
    $script = null;

    $traces = debug_backtrace();
    $file = __FILE__;

    for ($i = 0; $i < count($traces) && $file == __FILE__; ++$i)
    {
      $file = $traces[$i]['file'];
    }

    if (!is_file($file))
    {
      throw new RuntimeException('The script name from the traces is not valid: '.$file);
    }

    return $file;
  }

  /**
   * Constructor.
   *
   * Creates a backup of the given file with the extension .bak.
   *
   * This and the other methods must be static, because we should only register
   * static methods as shutdown functions. Otherwise the shutdown function
   * may not be executed when the associated object is not in memory anymore.
   */
  protected static function initialize($path, LimeTestRunner $testRunner)
  {
    self::$path = $path;
    self::$testRunner = $testRunner;

    if (file_exists($path.'.bak'))
    {
      unlink($path.'.bak');
    }

    rename($path, $path.'.bak');
    copy($path.'.bak', $path);

    // this is necessary to make sure the destructor is executed upon
    // fatal errors
    register_shutdown_function(array(__CLASS__, 'shutdown'));
  }

  /**
   * Destructor.
   *
   * Restores the backup created in the constructor.
   */
  protected static function shutdown()
  {
    if (file_exists(self::$path) && file_exists(self::$path.'.bak'))
    {
      unlink(self::$path);
      rename(self::$path.'.bak', self::$path);
    }
  }

  /**
   * Transforms the annotations in the script file and executes the resulting
   * script.
   */
  protected static function execute()
  {
    self::$lexer = new LimeLexerAnnotations(self::$path, self::$annotations);
    $callbacks = self::$lexer->parse(self::$path);

    self::includeTestFile();

    foreach ($callbacks as $annotation => $callbacks)
    {
      $addMethod = 'add'.$annotation;
      foreach ($callbacks as $callback)
      {
        self::$testRunner->$addMethod($callback);
      }
    }

    if (self::$test instanceof LimeTest)
    {
      self::$testRunner->addExceptionHandler(array(self::$test, 'handleException'));
      self::$testRunner->addErrorHandler(array(self::$test, 'handleException'));
      self::$testRunner->addAfter(array(self::$test, 'verifyException'));
    }

    self::$testRunner->run();
  }

  /**
   * Includes the test file in a separate scope.
   *
   * @param string $testVariable
   */
  protected static function includeTestFile()
  {
//    var_dump(file_get_contents(self::$path));
    include self::$path;

    if (!is_null(self::$lexer->getTestVariable()))
    {
      eval(sprintf('self::$test = %s;', self::$lexer->getTestVariable()));
    }
  }
}