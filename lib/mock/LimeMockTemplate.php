<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeMockTemplate
{
  private $parameters = array();
  private $filename = '';

  public function __construct($filename)
  {
    $this->filename = $filename;
  }

  public function render(array $parameters)
  {
    ob_start();
    extract($parameters);
    include $this->filename;

    return ob_get_clean();
  }

}