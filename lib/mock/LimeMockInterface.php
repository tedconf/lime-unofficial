<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

interface LimeMockInterface
{
  public function __construct($class, LimeMockBehaviourInterface $behaviour, LimeOutputInterface $output);

  public function __call($method, $parameters);

  public function __lime_replay();

  public function __lime_reset();

  /**
   * @return LimeMockStateInterface
   */
  public function __lime_getState();
}