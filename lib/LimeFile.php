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
 * A single file in a test suite.
 *
 * The file may be assigned to labels.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class LimeFile
{
  private
    $path          = null,
    $labels      = array();

  /**
   * Constructor.
   *
   * @param string $path  The path to the file
   */
  public function __construct($path)
  {
    $this->path = $path;
  }

  /**
   * Returns the path to the file.
   *
   * @return string
   */
  public function getPath()
  {
    return $this->path;
  }

  /**
   * Adds the given labels to the file.
   *
   * @param array $la bels
   */
  public function addLabels($labels)
  {
    $this->labels = array_merge($this->labels, $labels);
  }

  /**
   * Returns the labels of this file.
   *
   * @return array
   */
  public function getLabels()
  {
    return array_values(array_unique($this->labels));
  }
}