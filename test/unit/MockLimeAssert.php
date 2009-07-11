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
 * Mimics the behaviour of LimeTest for testing.
 *
 * The public properties $fails and $passes give you information about how
 * often a fail/pass was reported to this test instance.
 *
 * @package    sfLimeExtraPlugin
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class MockLimeAssert extends LimeTest
{
  /**
   * The number of reported failing tests
   * @var integer
   */
  public $fails = 0;

  /**
   * The number of reported passing tests
   * @var integer
   */
  public $passes = 0;

  /**
   * Constructor.
   */
  public function __construct()
  {
    parent::__construct(0, array('output' => new LimeOutputNone()));
  }

  /**
   * @see parent::ok()
   */
  public function ok($condition)
  {
    if (!$condition)
    {
      ++$this->fails;
    }
    else
    {
      ++$this->passes;
    }
  }

}
