<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

include dirname(__FILE__).'/../../bootstrap/unit.php';

LimeAnnotationSupport::enable();

$t = new LimeTest(5);


// @Test: assertEquals() throws an exception if exceptions don't match

  // fixtures
  $actual = new LimeTesterException(new Exception('Exception 1'));
  $expected = new LimeTesterException(new Exception('Exception 2'));
  // test
  $t->expect('LimeAssertionFailedException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws no exception if exceptions match

  // fixtures
  $actual = new LimeTesterException(new Exception('Exception 1'));
  $expected = new LimeTesterException(new Exception('Exception 1'));
  // test
  $actual->assertEquals($expected);