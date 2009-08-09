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
 * Provides an interface to execute PHP code or files.
 *
 * @package    lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class LimeShell
{
  const
    SUCCESS = 0,
    FAILED  = 1,
    UNKNOWN = 255;

  protected
    $executable = null;

  /**
   * Tries to find the system's PHP executable.
   *
   * @param $executable
   * @return unknown_type
   */
  protected static function findExecutable($executable = null)
  {
    if (is_null($executable))
    {
      if (getenv('PHP_PATH'))
      {
        $executable = getenv('PHP_PATH');

        if (!is_executable($executable))
        {
          throw new Exception('The defined PHP_PATH environment variable is not a valid PHP executable.');
        }
      }
      else
      {
        $executable = PHP_BINDIR.DIRECTORY_SEPARATOR.'php';
      }
    }

    if (is_executable($executable))
    {
      return $executable;
    }

    $path = getenv('PATH') ? getenv('PATH') : getenv('Path');
    $extensions = DIRECTORY_SEPARATOR == '\\' ? (getenv('PATHEXT') ? explode(PATH_SEPARATOR, getenv('PATHEXT')) : array('.exe', '.bat', '.cmd', '.com')) : array('');
    foreach (array('php5', 'php') as $executable)
    {
      foreach ($extensions as $extension)
      {
        foreach (explode(PATH_SEPARATOR, $path) as $dir)
        {
          $file = $dir.DIRECTORY_SEPARATOR.$executable.$extension;
          if (is_executable($file))
          {
            return $file;
          }
        }
      }
    }

    throw new Exception("Unable to find PHP executable.");
  }

  /**
   * Parses the given CLI arguments and returns an array of options.
   *
   * @param  array $arguments
   * @return array
   */
  public static function parseArguments(array $arguments)
  {
    $options = array();

    foreach ($GLOBALS['argv'] as $parameter)
    {
      if (preg_match('/^--([a-zA-Z\-]+)=(.+)$/', $parameter, $matches))
      {
        if (in_array($matches[2], array('true', 'false')))
        {
          $matches[2] = eval($matches[2]);
        }

        $options[$matches[1]] = $matches[2];
      }
      else if (preg_match('/^--([a-zA-Z\-]+)$/', $parameter, $matches))
      {
        $options[$matches[1]] = true;
      }
    }

    return $options;
  }

  /**
   * Constructor.
   *
   * @param $executable
   * @return unknown_type
   */
  public function __construct($executable = null)
  {
    $this->executable = self::findExecutable($executable);
  }

  /**
   * Executes a file or PHP code.
   *
   * The return value and the output is returned as array. If you pass the
   * PHP code as string, you must omit the opening "<?php" tag.
   *
   * @param  string $file       The PHP file or code
   * @param  array  $arguments  A list of command options and arguments
   *
   * @return array         An array with the return value as first and the
   *                       console output as second element.
   */
  public function execute($file, array $arguments = array())
  {
    if (!is_readable($file))
    {
      $tmpFile = tempnam(sys_get_temp_dir(), 'lime');
      file_put_contents($tmpFile, '<?php '.$file);
      $file = $tmpFile;
    }

    ob_start();
    passthru($this->buildCommand($file, $arguments), $return);
    $output = ob_get_clean();

    return array($return, $output);
  }

  public function executeCallback($callback, $file, array $arguments = array())
  {
    $handle = popen($this->buildCommand($file, $arguments), 'r');

    while (!feof($handle))
    {
      $line = fread($handle, 2048);
      call_user_func($callback, $line);
    }
  }

  protected function buildCommand($file, array $arguments = array())
  {
    foreach ($arguments as $argument => $value)
    {
      $arguments[$argument] = '--'.escapeshellarg($argument);

      if ($value !== true)
      {
        if (!is_string($value))
        {
          $value = var_export($value, true);
        }

        $arguments[$argument] .= '='.escapeshellarg($value);
      }
    }

    // see http://trac.symfony-project.org/ticket/5437 for the explanation on the weird "cd" thing
    return sprintf('cd & %s %s %s 2>&1', escapeshellarg($this->executable), escapeshellarg($file), implode(' ', $arguments));
  }
}