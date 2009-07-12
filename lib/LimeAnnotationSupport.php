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
    $annotations    = array('Test', 'Before', 'After', 'BeforeAll', 'AfterAll'),
    $enabled        = false;

  protected
    $path           = '',
    $backupPath     = '',
    $variables      = array(),
    $testVariable   = '',
    $callbacks      = array(),
    $testRunner     = null;

  public static function enable()
  {
    // make sure that annotations are not replaced twice at the same time
    if (!self::$enabled)
    {
      self::$enabled = true;
      new LimeAnnotationSupport(new LimeTestRunner());
    }
  }

  /**
   * Constructor.
   *
   * @see lime_test::__construct()
   */
  public function __construct(LimeTestRunner $testRunner)
  {
    $this->initialize($testRunner);
    $this->parse();
    $this->run();

    exit;
  }

  protected function initialize(LimeTestRunner $testRunner)
  {
    register_shutdown_function(array($this, 'shutdown'));

    $this->path = $this->getScriptPath();
    $this->backupPath = $this->path.'.bak';
    $this->testRunner = $testRunner;

    rename($this->path, $this->backupPath);
  }

  /**
   * Transforms the annotated test script into an executable script.
   *
   * All code wrapped in annotations is wrapped into functions automatically.
   */
  protected function parse()
  {
    $content = file_get_contents($this->backupPath);
    $file = fopen($this->path, 'w');
    $inFunctionBlock = false;
    $functionCount = 0;

    // collect variables
    if (preg_match_all('/\$\w+/', $content, $matches))
    {
      $this->variables = array_diff(array_unique(array_merge($this->variables, $matches[0])), array('$this'));
    }

    // TODO: improve this code, does not work if "<?php" is preset multiple times
    $content = str_replace('<?php', '<?php global '.implode(', ', $this->variables).';', $content);

    // comment classes, interfaces and functions out
    if (preg_match_all('/(((abstract\s+)?class|interface)\s[\w\s]+\s*|function\s+\w+\s*\([^)]*\)\s*)(\{([^{}]*|(?4))*\})/si', $content, $matches))
    {
      foreach ($matches[0] as $block)
      {
        $content = str_replace($block, '/*'.$block.'*/', $content);
      }
    }

    // remove multiline comments
    if (preg_match_all('/\/\*.+\*\//sU', $content, $matches))
    {
      foreach ($matches[0] as $block)
      {
        // we need to preserve line breaks
        $newBlock = preg_replace('/[^\n]+/', '', $block);
        $content = str_replace($block, $newBlock, $content);
      }
    }

    // process lines
    foreach (explode("\n", $content) as $line)
    {
      // annotation
      if (preg_match('/^\s*\/\/\s*@(\w+)([:\s]+(.*))?\s*$/', $line, $matches))
      {
        $unknownAnnotation = false;
        $annotation = $matches[1];
        $data = count($matches) > 3 ? trim($matches[3]) : '';

        if (!in_array($annotation, self::$annotations))
        {
          throw new LogicException(sprintf('The annotation "%s" is not valid', $annotation));
        }

        if (!array_key_exists($annotation, $this->callbacks))
        {
          $this->callbacks[$annotation] = array();
        }

        $this->callbacks[$annotation][] = $function = '__lime_annotation_'.($functionCount++);

        if ($inFunctionBlock)
        {
          fwrite($file, '} ');
        }

        $variables = implode(', ', $this->variables);
        $line = "function $function() { global $variables;";
        $inFunctionBlock = true;
//        if (!empty($data))
//        {
//          $line .= ' '.$this->testVariable.'->comment("'.$data.'");';
//        }
      }
      /*
      // tester instantiation
      else if (strpos($line, 'new LimeTest') !== false)
      {
        // register tester
        if (!preg_match('/(\$\w+)\s*=\s*new LimeTest/', $line, $matches))
        {
          throw new RuntimeException('The "LimeTest" class must be assigned to a variable');
        }

        $this->testVariable = $matches[1];

        // initialize variables instead
        $variables = $this->variables;
        foreach ($variables as $key => $variable)
        {
          $variables[$key] .= ' = null';
        }
        $line = 'global '.implode(', ', $this->variables).'; '.$this->testVariable." = \$this;";
      }
      */

      fwrite($file, $line."\n");
    }

    if ($inFunctionBlock)
    {
      fwrite($file, '} ');
    }

    fclose($file);
  }

  protected function run()
  {
//    var_dump(file_get_contents($this->path));
    include $this->path;

    // register callbacks now that they are declared
    foreach ($this->callbacks as $annotation => $callbacks)
    {
      $addMethod = 'add'.$annotation;
      foreach ($callbacks as $callback)
      {
        $this->testRunner->$addMethod($callback);
      }
    }

    $this->testRunner->run();
  }

  /**
   * Removes the transformed test script and restores the original test script.
   */
  public function shutdown()
  {
    if (file_exists($this->path) && file_exists($this->backupPath))
    {
      unlink($this->path);
      rename($this->backupPath, $this->path);
    }
  }

  /**
   * Returns the file path of the executed test script
   *
   * @return string  The file path
   */
  protected function getScriptPath()
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
}