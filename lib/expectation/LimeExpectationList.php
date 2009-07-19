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
 * An ordered expectation collection where the amount of elements is important.
 *
 * This implementation of LimeExpectationCollection compares expected
 * and actual values taking their order into account. It is also important how
 * often a value was expected and added.
 *
 * The following example will not verify successfully:
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
 * The following other example will not verify either:
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
class LimeExpectationList extends LimeExpectationCollection
{

  /**
   * The cursor pointing on the currently expected value
   * @var integer
   */
  protected $cursor = 0;

  /**
   * (non-PHPdoc)
   * @see lib/expectation/LimeExpectationCollection#isExpected($value)
   */
  protected function isExpected($value)
  {
    return array_key_exists($this->cursor, $this->expected) && $this->expected[$this->cursor++] == $value;
  }

}