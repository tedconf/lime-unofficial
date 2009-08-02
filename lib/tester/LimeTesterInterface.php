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

  public function assertEquals(LimeTesterInterface $expected);

  public function assertNotEquals(LimeTesterInterface $expected);

  public function assertSame(LimeTesterInterface $expected);

  public function assertNotSame(LimeTesterInterface $expected);

  public function assertLike(LimeTesterInterface $expected);

  public function assertUnlike(LimeTesterInterface $expected);

  public function assertGreaterThan(LimeTesterInterface $expected);

  public function assertGreaterThanOrEqual(LimeTesterInterface $expected);

  public function assertLessThan(LimeTesterInterface $expected);

  public function assertLessThanOrEqual(LimeTesterInterface $expected);

  public function assertContains(LimeTesterInterface $expected);

  public function assertNotContains(LimeTesterInterface $expected);
}