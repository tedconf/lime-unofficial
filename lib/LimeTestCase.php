<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeTestCase extends LimeAssert
{
  public function __construct($plan = null, array $options = array())
  {
    parent::__construct($plan, $options);

    $this->testRunner = new LimeTestRunner();
    $this->testRunner->addBefore(array($this, 'setUp'));
    $this->testRunner->addAfter(array($this, 'tearDown'));

    foreach (get_class_methods($this) as $method)
    {
      if (strpos($method, 'test') === 0)
      {
        $this->testRunner->addTest(array($this, $method));
      }
    }
  }

  public function setUp() {}

  public function tearDown() {}

  public function run()
  {
    $this->testRunner->run();
  }
}