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

$t = new LimeTest(79);


// @Before

  $printer = LimeMock::createStrict('LimePrinter', $t);
  $output = new LimeOutputConsoleSummary($printer);


// @After

  $printer = null;
  $output = null;


// @Test: The number of tests is updated on every input

  // fixtures
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("1\r", LimePrinter::OK);
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("2\r", LimePrinter::OK);
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("3\r", LimePrinter::OK);
  $printer->replay();
  // test
  $output->start('/test/script');
  $output->pass('A passed test', '/test/script', 11);
  $output->pass('A passed test', '/test/script', 22);
  $output->pass('A passed test', '/test/script', 33);
  // assertions
  $printer->verify();


// @Test: Once any test has failed, the style of the number is LimePrinter::NOT_OK

  // fixtures
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("1\r", LimePrinter::OK);
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("2\r", LimePrinter::NOT_OK);
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("3\r", LimePrinter::NOT_OK);
  $printer->replay();
  // test
  $output->start('/test/script');
  $output->pass('A passed test', '/test/script', 11);
  $output->fail('A failed test', '/test/script', 22);
  $output->pass('A passed test', '/test/script', 33);
  // assertions
  $printer->verify();


// @Test: Once a warning has appeared, the style of the number is LimePrinter::WARNING

  // fixtures
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("1\r", LimePrinter::OK);
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("2\r", LimePrinter::WARNING);
  $printer->replay();
  // test
  $output->start('/test/script');
  $output->pass('A passed test', '/test/script', 11);
  $output->warning('A warning', '/test/script', 22);
  $output->pass('A passed test', '/test/script', 33);
  // assertions
  $printer->verify();


// @Test: Once an error has appeared, the style of the number is LimePrinter::NOT_OK

  // fixtures
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("1\r", LimePrinter::OK);
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("2\r", LimePrinter::NOT_OK);
  $printer->replay();
  // test
  $output->start('/test/script');
  $output->pass('A passed test', '/test/script', 11);
  $output->error(new LimeError('An error', '/test/script', 22));
  $output->pass('A passed test', '/test/script', 33);
  // assertions
  $printer->verify();


// @Test: If both a warning and an error appeared, the style is LimePrinter::NOT_OK

  // fixtures
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("1\r", LimePrinter::OK);
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("2\r", LimePrinter::NOT_OK);
  $printer->replay();
  // test
  $output->start('/test/script');
  $output->pass('A passed test', '/test/script', 11);
  $output->error(new LimeError('An error', '/test/script', 22));
  $output->warning('A warning', '/test/script', 33);
  $output->pass('A passed test', '/test/script', 44);
  // assertions
  $printer->verify();


// @Test: When start() is called again, a new line for the new test is started

  // fixtures
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("1\r", LimePrinter::OK);
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printLine("ok", LimePrinter::OK);
  $printer->replay();
  // test
  $output->start('/test/script');
  $output->pass('A passed test', '/test/script', 11);
  $output->start('/test/script2');
  // assertions
  $printer->verify();


// @Test: When start() is called again and tests failed, the status is "not ok"

  // fixtures
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("1\r", LimePrinter::NOT_OK);
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printLine("not ok", LimePrinter::NOT_OK);
  $printer->any('printText');
  $printer->any('printLine');
  $printer->replay();
  // test
  $output->start('/test/script');
  $output->fail('A failed test', '/test/script', 11);
  $output->start('/test/script2');
  // assertions
  $printer->verify();


// @Test: When start() is called again and warnings appeared in the test, the status is warning

  // fixtures
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("1\r", LimePrinter::OK);
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printLine("warning", LimePrinter::WARNING);
  $printer->any('printText');
  $printer->any('printLine');
  $printer->replay();
  // test
  $output->start('/test/script');
  $output->pass('A passed test', '/test/script', 11);
  $output->warning('A warning', '/test/script', 33);
  $output->start('/test/script2');
  // assertions
  $printer->verify();


// @Test: When start() is called again and errors appeared in the test, the status is "not ok"

  // fixtures
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("1\r", LimePrinter::OK);
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printLine("not ok", LimePrinter::NOT_OK);
  $printer->any('printText');
  $printer->any('printLine');
  $printer->replay();
  // test
  $output->start('/test/script');
  $output->pass('A passed test', '/test/script', 11);
  $output->error(new LimeError('An error', '/test/script', 22));
  $output->start('/test/script2');
  // assertions
  $printer->verify();


// @Test: When start() is called again, the styles are reset

  // fixtures
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("1\r", LimePrinter::OK);
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printLine("not ok", LimePrinter::NOT_OK);
  $printer->any('printText');
  $printer->any('printLine');
  $printer->printText(str_pad('/test/script2', 73, '.'));
  $printer->printText("1\r", LimePrinter::OK);
  $printer->replay();
  // test
  $output->start('/test/script');
  $output->pass('A passed test', '/test/script', 11);
  $output->error(new LimeError('An error', '/test/script', 22));
  $output->start('/test/script2');
  $output->pass('A passed test', '/test/script2', 11);
  // assertions
  $printer->verify();


// @Test: When start() is called again and the plan did not match, a message is printed

  // fixtures
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printText("1\r", LimePrinter::OK);
  $printer->printText(str_pad('/test/script', 73, '.'));
  $printer->printLine("not ok", LimePrinter::NOT_OK);
  $printer->printLine('    Looks like you planned 2 tests but only ran 1.');
  $printer->replay();
  // test
  $output->start('/test/script');
  $output->plan(2);
  $output->pass('A passed test', '/test/script', 11);
  $output->start('/test/script2');
  // assertions
  $printer->verify();


// @Test: When start() is called again and anything failed, detailed statistics are printed

  // fixtures
  $printer->any('printText');
  $printer->any('printLine');
  $printer->printText('    ');
  $printer->printText('Passed: 1');
  $printer->any('printText')->once();
  $printer->printText('Failed: 2', LimePrinter::NOT_OK);
  $printer->any('printText')->once();
  $printer->printText('Warnings: 1', LimePrinter::WARNING);
  $printer->any('printText')->once();
  $printer->printLine('Errors: 2', LimePrinter::NOT_OK);
  $printer->any('printLine');
  $printer->replay();
  // test
  $output->start('/test/script');
  $output->pass('A passed test', '/test/script', 11);
  $output->fail('A failed test', '/test/script', 11);
  $output->fail('A failed test', '/test/script', 11);
  $output->warning('A warning', '/test/script', 11);
  $output->error(new LimeError('An error', '/test/script', 11));
  $output->error(new LimeError('An error', '/test/script', 11));
  $output->start('/test/script2');
  // assertions
  $printer->verify();


// @Test: flush() prints a summary of all files if failures occured

  // fixtures
  $printer = LimeMock::create('LimePrinter', $t); // non-strict
  $printer->any('printText');
  $printer->any('printLine');
  $printer->printBox(' Failed 2/5 test scripts, 60.00% okay. 1/5 subtests failed, 80.00% okay.', LimePrinter::NOT_OK);
  $printer->replay();
  $output = new LimeOutputConsoleSummary($printer);
  // test
  $output->start('/test/script1');
  $output->pass('A passed test', '/test/script', 11);
  $output->start('/test/script2');
  $output->pass('A passed test', '/test/script2', 11);
  $output->warning('A warning', '/test/script2', 11);
  $output->start('/test/script3');
  $output->fail('A failed test', '/test/script3', 11);
  $output->start('/test/script4');
  $output->pass('A passed test', '/test/script', 11);
  $output->error(new LimeError('An error', '/test/script', 11));
  $output->start('/test/script5');
  $output->pass('A passed test', '/test/script', 11);
  $output->flush();
  // assertions
  $printer->verify();


// @Test: flush() prints a success message if everything went fine

  // fixtures
  $printer = LimeMock::create('LimePrinter', $t); // non-strict
  $printer->any('printText');
  $printer->any('printLine');
  $printer->printBox(' All tests successful.', LimePrinter::HAPPY);
  $printer->printBox(' Files=2, Tests=3, Time=00:01', LimePrinter::HAPPY);
  $printer->replay();
  $output = new LimeOutputConsoleSummary($printer);
  // test
  $output->start('/test/script1');
  $output->pass('A passed test', '/test/script1', 11);
  $output->start('/test/script2');
  $output->pass('A passed test', '/test/script2', 11);
  $output->pass('A passed test', '/test/script2', 11);
  $output->flush();
  // assertions
  $printer->verify();


// @Test: If the base dir is set, the test files are truncated

  // fixtures
  $output = new LimeOutputConsoleSummary($printer, '/test');
  $printer->reset();
  $printer->printText(str_pad('/script', 73, '.'));
  $printer->printText("1\r", LimePrinter::OK);
  $printer->replay();
  // test
  $output->start('/test/script');
  $output->pass('A passed test', '/test/script', 11);
  // assertions
  $printer->verify();
