<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class sfLimeRegistration
{
  public
    $files      = array(),
    $extension  = '.php',
    $baseDir    = '';

  public function register($filesOrDirectories)
  {
    foreach ((array) $filesOrDirectories as $fileOrDirectory)
    {
      if (is_file($fileOrDirectory))
      {
        $this->files[] = realpath($fileOrDirectory);
      }
      elseif (is_dir($fileOrDirectory))
      {
        $this->registerDir($fileOrDirectory);
      }
      else
      {
        throw new Exception(sprintf('The file or directory "%s" does not exist.', $fileOrDirectory));
      }
    }
  }

  public function registerGlob($glob)
  {
    if ($dirs = glob($glob))
    {
      foreach ($dirs as $file)
      {
        $this->files[] = realpath($file);
      }
    }
  }

  public function registerDir($directory)
  {
    if (!is_dir($directory))
    {
      throw new Exception(sprintf('The directory "%s" does not exist.', $directory));
    }

    $files = array();

    $currentDir = opendir($directory);
    while ($entry = readdir($currentDir))
    {
      if ($entry == '.' || $entry == '..') continue;

      if (is_dir($entry))
      {
        $this->registerDir($entry);
      }
      elseif (preg_match('#'.$this->extension.'$#', $entry))
      {
        $files[] = realpath($directory.DIRECTORY_SEPARATOR.$entry);
      }
    }

    $this->files = array_merge($this->files, $files);
  }

  protected function getRelativeFile($file)
  {
    return str_replace(DIRECTORY_SEPARATOR, '/', str_replace(array(realpath($this->baseDir).DIRECTORY_SEPARATOR, $this->extension), '', $file));
  }
}