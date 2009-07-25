<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

interface LimeTesterInterface
{
  public function __toString();

  public function assertEquals($expected, $strict = false);

  public function assertNotEquals($expected, $strict = false);

  public function assertLike($expected);

  public function assertUnlike($expected);

  public function assertGreaterThan($expected);

  public function assertGreaterThanOrEqual($expected);

  public function assertLessThan($expected);

  public function assertLessThanOrEqual($expected);
}