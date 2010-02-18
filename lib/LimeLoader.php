<?php

/*
 * This file is part of the Lime framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Bernhard Schussek <bernhard.schussek@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Scans the file systems for test file.
 *
 * All files registered in the configuration, that is passed to the constructor,
 * will be loaded. The file paths can then be accessed using the different
 * getFile*() methods.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class LimeLoader
{
  private
    $files          = array(),
    $labels         = array(),
    $filesByName    = array(),
    $configuration  = null;

  /**
   * Loads all tests registered in the given configuration.
   *
   * @param LimeConfiguration $configuration
   */
  public function __construct(LimeConfiguration $configuration)
  {
    $this->configuration = $configuration;

    foreach ($configuration->getRegisteredFiles() as $file)
    {
      $this->loadFile($this->getAbsolutePath($file[0]), $file[1], $file[2]);
    }
    foreach ($configuration->getRegisteredDirs() as $dir)
    {
      $this->loadDir($this->getAbsolutePath($dir[0]), $dir[1], $dir[2]);
    }
    foreach ($configuration->getRegisteredGlobs() as $glob)
    {
      $this->loadGlob($this->getAbsolutePath($glob[0]), $glob[1], $glob[2]);
    }
    foreach ($configuration->getRegisteredCallbacks() as $callback)
    {
      $this->loadFile($callback[0], $callback[1], $callback[2]);
    }
  }

  /**
   * Returns the absolute version of the given path.
   *
   * If the path is not already absolute, the base directory of the
   * configuration is prepended in front of the path.
   *
   * @param  string $path
   * @return string
   */
  protected function getAbsolutePath($path)
  {
    if (realpath($path) != $path)
    {
      return $this->configuration->getBaseDir().DIRECTORY_SEPARATOR.$path;
    }
    else
    {
      return $path;
    }
  }

  /**
   * Registers a test file path in the test suite.
   *
   * @param string $path
   * @param array $labels
   */
  protected function loadFile($path, LimeExecutable $executable, $labels = array())
  {
    if (!is_file($path))
    {
      throw new InvalidArgumentException(sprintf('The file "%s" does not exist', $path));
    }

    $path = realpath($path);
    $name = basename($path, $this->configuration->getSuffix());

    if (!isset($this->files[$path]))
    {
      $this->files[$path] = new LimeFile($path, $executable);

      if (!isset($this->filesByName[$name]))
      {
        $this->filesByName[$name] = array();
      }

      // allow multiple files with the same name
      $this->filesByName[$name][] = $this->files[$path];
    }

    $labels = (array)$labels;

    $this->files[$path]->addLabels($labels);

    foreach ($labels as $label)
    {
      if (!isset($this->labels[$label]))
      {
        $this->labels[$label] = new LimeLabel();
      }

      $this->labels[$label]->addFile($this->files[$path]);
    }
  }

  /**
   * Loads an array of test file paths in the test suite.
   *
   * @param array $paths
   * @param array $labels
   */
  protected function loadFiles(array $paths, LimeExecutable $executable, $labels = array())
  {
    foreach ($paths as $path)
    {
      if (is_dir($path))
      {
        $this->loadDir($path, $executable, $labels);
      }
      else
      {
        $this->loadFile($path, $executable, $labels);
      }
    }
  }

  /**
   * Loads the content of a directory in the test suite.
   *
   * Only files with the configures suffix will be added.
   *
   * @param string $path
   * @param array $labels
   */
  protected function loadDir($path, LimeExecutable $executable, $labels = array())
  {
    $iterator = new DirectoryIterator($path);

    foreach ($iterator as $file)
    {
      if (!$file->isDot())
      {
        if ($file->isDir())
        {
          $this->loadDir($file->getPathname(), $executable, $labels);
        }
        else if (preg_match($this->configuration->getFilePattern(), $file->getFilename()))
        {
          $this->loadFile($file->getPathname(), $executable, $labels);
        }
      }
    }
  }

  /**
   * Loads all files matched by the given glob in the test suite.
   *
   * @param string $glob
   * @param array $labels
   */
  protected function loadGlob($glob, LimeExecutable $executable, $labels = array())
  {
    if ($files = glob($glob))
    {
      $this->loadFiles($files, $executable, $labels);
    }
  }

  /**
   * Adds the results of a callback to be added to the test suite.
   *
   * The callback should return an array of file paths.
   *
   * @param callable $callback
   * @param array $labels
   */
  protected function loadCallback($callback, LimeExecutable $executable, $labels = array())
  {
    if ($files = call_user_func($callback))
    {
      $this->loadFiles($files, $executable, $labels);
    }
  }

  public function isLabel($label)
  {
    preg_match('/^[+-]?(.+)$/', $label, $matches);

    return isset($this->labels[$matches[1]]);
  }

  /**
   * Returns all files with the given labels.
   *
   * @param  array $labels
   * @return array
   */
  public function getFilesByLabels(array $labels = array())
  {
    $time = microtime();
    $result = new LimeLabel();

    foreach ($this->files as $file)
    {
      $result->addFile($file);
    }

    if (count($labels) > 0)
    {
      foreach ($labels as $label)
      {
        if (!preg_match('/^([-+]?)(.+)$/', $label, $matches))
        {
          throw new InvalidArgumentException(sprintf('Invalid label format: "%s"', $label));
        }

        $operation = $matches[1];
        $label = $matches[2];

        if (!isset($this->labels[$label]))
        {
          throw new InvalidArgumentException(sprintf('Unknown label: "%s"', $label));
        }

        if ($operation == '+')
        {
          $result = $result->add($this->labels[$label]);
        }
        else if ($operation == '-')
        {
          $result = $result->subtract($this->labels[$label]);
        }
        else
        {
          $result = $result->intersect($this->labels[$label]);
        }
      }
    }

    return $result->getFiles();
  }

  /**
   * Returns all files with the given name.
   *
   * @param  string $name
   * @return array
   */
  public function getFilesByName($name)
  {
    if (!isset($this->filesByName[$name]))
    {
      throw new InvalidArgumentException(sprintf('Unknown test: "%s"', $name));
    }

    return $this->filesByName[$name];
  }

  /**
   * Returns the file with the given path.
   *
   * @param  string $path
   * @return LimeFile
   */
  public function getFileByPath($path)
  {
    if (!isset($this->files[$path]))
    {
      throw new InvalidArgumentExceptoin(sprintf('Unknown file: "%s"', $path));
    }

    return $this->files[$path];
  }
}