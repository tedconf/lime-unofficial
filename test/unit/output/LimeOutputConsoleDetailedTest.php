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

$t = new LimeTest(33);

// @Before

  $printer = LimeMock::createStrict('LimePrinter', $t);
  $output = new LimeOutputConsoleDetailed($printer);


// @After

  $printer = null;
  $output = null;


// @Test: plan() prints the amount of planned tests

  // fixtures
  $printer->printLine('1..2');
  $printer->replay();
  // test
  $output->plan(2, '/test/file');
  // assertions
  $printer->verify();


// @Test: pass() prints and counts passed tests

  // fixtures
  $printer->printText('ok 1', LimePrinter::OK);
  $printer->printLine(' - A passed test');
  $printer->printText('ok 2', LimePrinter::OK);
  $printer->printLine(' - Another passed test');
  $printer->replay();
  // test
  $output->pass('A passed test', '/test/file', 11);
  $output->pass('Another passed test', '/test/file', 22);
  // assertions
  $printer->verify();


// @Test: pass() prints no message if none is given

  // fixtures
  $printer->printLine('ok 1', LimePrinter::OK);
  $printer->replay();
  // test
  $output->pass('', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: fail() prints and counts failed tests

  // fixtures
  $printer->printText('not ok 1', LimePrinter::NOT_OK);
  $printer->printLine(' - A failed test');
  $printer->printLine('#     Failed test (/test/file at line 33)', LimePrinter::COMMENT);
  $printer->printText('not ok 2', LimePrinter::NOT_OK);
  $printer->printLine(' - Another failed test');
  $printer->printLine('#     Failed test (/test/file at line 55)', LimePrinter::COMMENT);
  $printer->printLine('#       error', LimePrinter::COMMENT);
  $printer->printLine('#       message', LimePrinter::COMMENT);
  $printer->replay();
  // test
  $output->fail('A failed test', '/test/file', 33);
  $output->fail('Another failed test', '/test/file', 55, "error\nmessage");
  // assertions
  $printer->verify();


// @Test: fail() prints no message if none is given

  // fixtures
  $printer->printLine('not ok 1', LimePrinter::NOT_OK);
  $printer->printLine('#     Failed test (/test/file at line 11)', LimePrinter::COMMENT);
  $printer->replay();
  // test
  $output->fail('', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: skip() prints and counts skipped tests

  // fixtures
  $printer->printText('skip 1', LimePrinter::SKIP);
  $printer->printLine(' - A skipped test');
  $printer->printText('skip 2', LimePrinter::SKIP);
  $printer->printLine(' - Another skipped test');
  $printer->replay();
  // test
  $output->skip('A skipped test', '/test/file', 11);
  $output->skip('Another skipped test', '/test/file', 22);
  // assertions
  $printer->verify();


// @Test: skip() prints no message if none is given

  // fixtures
  $printer->printLine('skip 1', LimePrinter::SKIP);
  $printer->replay();
  // test
  $output->skip('', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: warning() prints a warning

  // fixtures
  $printer->printBox(' A very important warning', LimePrinter::WARNING);
  $printer->replay();
  // test
  $output->warning('A very important warning', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: error() prints an warning

  // fixtures
  $printer->printBox(' A very important error', LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->error('A very important error', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: comment() prints a comment

  // fixtures
  $printer->printLine('# My comment', LimePrinter::COMMENT);
  $printer->replay();
  // test
  $output->comment('My comment', '/test/file', 11);
  // assertions
  $printer->verify();


// @Test: flush() prints a summary

  // @Test: Case 1 - Too many tests

  // fixtures
  $output->plan(1, '/test/file');
  $output->pass('First test', '/test/file', 11);
  $output->pass('First test', '/test/file', 22);
  $printer->reset();
  $printer->printBox(' Looks like you planned 1 tests but ran 1 extra.', LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 2 - Too few tests

  // fixtures
  $output->plan(2, '/test/file');
  $output->pass('First test', '/test/file', 11);
  $printer->reset();
  $printer->printBox(' Looks like you planned 2 tests but only ran 1.', LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 3 - Correct number of tests

  // fixtures
  $output->plan(1, '/test/file');
  $output->pass('First test', '/test/file', 11);
  $printer->reset();
  $printer->printBox(' Looks like everything went fine.', LimePrinter::HAPPY);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 4 - Failed tests

  // fixtures
  $output->plan(3, '/test/file');
  $output->pass('First test', '/test/file', 11);
  $output->fail('Second test', '/test/file', 22);
  $output->pass('Third test', '/test/file', 33);
  $printer->reset();
  $printer->printBox(' Looks like you failed 1 tests of 3.', LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 5 - Failed and too few tests

  // fixtures
  $output->plan(3, '/test/file');
  $output->pass('First test', '/test/file', 11);
  $output->fail('Second test', '/test/file', 22);
  $printer->reset();
  $printer->printBox(' Looks like you failed 1 tests of 2.', LimePrinter::ERROR);
  $printer->printBox(' Looks like you planned 3 tests but only ran 2.', LimePrinter::ERROR);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 6 - No plan

  // fixtures
  $output->pass('First test', '/test/file', 11);
  $printer->reset();
  $printer->printLine('1..1');
  $printer->printBox(' Looks like everything went fine.', LimePrinter::HAPPY);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();

  // @Test: Case 7 - Skipped tests

  // fixtures
  $output->plan(1, '/test/file');
  $output->skip('First test', '/test/file', 11);
  $printer->reset();
  $printer->printBox(' Looks like everything went fine.', LimePrinter::HAPPY);
  $printer->replay();
  // test
  $output->flush();
  // assertions
  $printer->verify();
