<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require_once dirname(__FILE__).'/../../bootstrap/unit.php';

LimeAnnotationSupport::enable();

$t = new LimeTest(8);


// @Before

  $output = new LimeOutputRaw();


// @After

  $output = null;


// @Test: plan() prints the method call as serialized array

  // test
  ob_start();
  $output->plan(1, '/test/file');
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('plan', array(1, '/test/file')))."\n", 'The method call is serialized');


// @Test: pass() prints the method call as serialized array

  // test
  ob_start();
  $output->pass('A passed test', '/test/file', 11);
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('pass', array('A passed test', '/test/file', 11)))."\n", 'The method call is serialized');


// @Test: fail() prints the method call as serialized array

  // test
  ob_start();
  $output->fail('A failed test', '/test/file', 11, 'Error message');
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('fail', array('A failed test', '/test/file', 11, 'Error message')))."\n", 'The method call is serialized');


// @Test: skip() prints the method call as serialized array

  // test
  ob_start();
  $output->skip('A skipped test', '/test/file', 11);
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('skip', array('A skipped test', '/test/file', 11)))."\n", 'The method call is serialized');


// @Test: warning() prints the method call as serialized array

  // test
  ob_start();
  $output->warning('A warning', '/test/file', 11);
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('warning', array('A warning', '/test/file', 11)))."\n", 'The method call is serialized');


// @Test: error() prints the method call as serialized array

  // test
  ob_start();
  $output->error('An error', '/test/file', 11);
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('error', array('An error', '/test/file', 11)))."\n", 'The method call is serialized');


// @Test: comment() prints the method call as serialized array

  // test
  ob_start();
  $output->comment('A comment');
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('comment', array('A comment')))."\n", 'The method call is serialized');


// @Test: flush() prints the method call as serialized array

  // test
  ob_start();
  $output->flush();
  $result = ob_get_clean();
  // assertions
  $t->is($result, serialize(array('flush', array()))."\n", 'The method call is serialized');