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
 * An unordered expectation collection where the amount of elements is important.
 *
 * This implementation of LimeExpectationCollection compares expected
 * and actual values ignoring their order. It is important though how often
 * a value was expected and added.
 *
 * The following example will verify successfully:
 *
 * <code>
 *   $list = new lime_expectationList($t);
 *   $list->addExpected(1);
 *   $list->addExpected(2);
 *   $list->addActual(2);
 *   $list->addActual(1);
 *   $list->verify();
 * </code>
 *
 * The following other example will not verify:
 *
 * <code>
 *   $list = new lime_expectationList($t);
 *   $list->addExpected(1);
 *   $list->addActual(1);
 *   $list->addActual(1);
 *   $list->verify();
 * </code>
 *
 * @package    lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class LimeExpectationBag extends LimeExpectationCollection
{

  /**
   * (non-PHPdoc)
   * @see lib/expectation/LimeExpectationCollection#isExpected($value)
   */
  protected function isExpected($value)
  {
      $actual = $this->count($value, $this->actual);
      $expected = $this->count($value, $this->expected);

      return $actual < $expected;
  }

  /**
   * (non-PHPdoc)
   * @see lib/expectation/LimeExpectationCollection#verify()
   */
  public function verify()
  {
    sort($this->actual);
    sort($this->expected);

    parent::verify();
  }

  /**
   * Counts how often the given value occurs in the given array.
   * @param array $array
   * @param $value
   * @return unknown_type
   */
  private function count($value, array $array)
  {
    $amount = 0;

    for ($i = 0; $i < count($array); ++$i)
    {
      if ($this->strict ? $array[$i] === $value : $array[$i] == $value)
      {
        ++$amount;
      }
    }

    return $amount;
  }

}