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

$t = new LimeTest(14);


// @Before

  $output = new LimeOutputInspectable();


// @After

  $output = null;


// @Test: getPassed() returns the number of calls to pass()

  $output->pass('A passed test', '/test/script', 11);
  $output->fail('A failed test', '/test/script', 11);
  $output->pass('A passed test', '/test/script', 11);
  $t->is($output->getPassed(), 2, 'The returned number is correct');


// @Test: getFailed() returns the number of calls to fail()

  $output->fail('A failed test', '/test/script', 11);
  $output->pass('A passed test', '/test/script', 11);
  $output->fail('A failed test', '/test/script', 11);
  $t->is($output->getFailed(), 2, 'The returned number is correct');


// @Test: getSkipped() returns the number of calls to skip()

  $output->skip('A skipped test', '/test/script', 11);
  $output->pass('A passed test', '/test/script', 11);
  $output->skip('A skipped test', '/test/script', 11);
  $t->is($output->getSkipped(), 2, 'The returned number is correct');


// @Test: getErrors() returns the number of calls to error()

  $output->error('An error', '/test/script', 11);
  $output->pass('A passed test', '/test/script', 11);
  $output->error('An error', '/test/script', 11);
  $t->is($output->getErrors(), 2, 'The returned number is correct');


// @Test: getWarnings() returns the number of calls to warning()

  $output->warning('A warning', '/test/script', 11);
  $output->pass('A passed test', '/test/script', 11);
  $output->warning('A warning', '/test/script', 11);
  $t->is($output->getWarnings(), 2, 'The returned number is correct');


// @Test: Method calls are forwarded to a decorated object

  // fixtures
  $mock = LimeMock::create('LimeOutputInterface', $t);
  $mock->start('/test/file');
  $mock->pass('A passed test', '/test/script', 11);
  $mock->fail('A failed test', '/test/script', 11, 'The error');
  $mock->skip('A skipped test', '/test/script', 11);
  $mock->warning('A warning', '/test/script', 11);
  $mock->error('An error', '/test/script', 11);
  $mock->comment('A comment');
  $mock->info('An info');
  $mock->flush();
  $mock->replay();
  $output = new LimeOutputInspectable($mock);
  // test
  $output->start('/test/file');
  $output->pass('A passed test', '/test/script', 11);
  $output->fail('A failed test', '/test/script', 11, 'The error');
  $output->skip('A skipped test', '/test/script', 11);
  $output->warning('A warning', '/test/script', 11);
  $output->error('An error', '/test/script', 11);
  $output->comment('A comment');
  $output->info('An info');
  $output->flush();
  // assertions
  $mock->verify();