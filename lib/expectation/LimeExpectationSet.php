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
 * An ordered expectation collection where the amount of elements is not
 * important.
 *
 * This implementation of LimeExpectationCollection compares expected
 * and actual values ignoring their order. This class also does not care
 * if a value is added more than once.
 *
 * The following example will verify successfully:
 *
 * <code>
 *   $list = new lime_expectationList($t);
 *   $list->addExpected(1);
 *   $list->addExpected(2);
 *   $list->addActual(2);
 *   $list->addActual(1);
 *   $list->addActual(1);
 *   $list->verify();
 * </code>
 *
 * @package    lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class LimeExpectationSet extends LimeExpectationCollection
{

  /**
   * (non-PHPdoc)
   * @see lib/expectation/LimeExpectationCollection#isExpected($value)
   */
  protected function isExpected($value)
  {
    return in_array($value, $this->expected);
  }

  /**
   * (non-PHPdoc)
   * @see lib/expectation/LimeExpectationCollection#verify()
   */
  public function verify()
  {
    sort($this->actual);
    sort($this->expected);

    $this->actual = array_unique($this->actual);
    $this->expected = array_unique($this->expected);

    parent::verify();
  }

}