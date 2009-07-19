<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require_once dirname(__FILE__).'/../bootstrap/unit.php';

LimeAnnotationSupport::enable();

$t = new LimeTest(9);

// @Before

  $colorizer = LimeMock::create('LimeColorizer');
  $printer = new LimePrinter($colorizer);


// @After

  $colorizer = null;
  $printer = null;


// @Test: printText() prints text using the given style

  // fixtures
  $colorizer->colorize('My text', 'RED')->returns('<RED>My text</RED>');
  $colorizer->replay();
  // test
  ob_start();
  $printer->printText('My text', 'RED');
  $result = ob_get_clean();
  // assertions
  $t->is($result, '<RED>My text</RED>', 'The result was colorized and printed');


// @Test: printLine() prints text followed by a newline

  // fixtures
  $colorizer->colorize('My text', 'RED')->returns('<RED>My text</RED>');
  $colorizer->replay();
  // test
  ob_start();
  $printer->printLine('My text', 'RED');
  $result = ob_get_clean();
  // assertions
  $t->is($result, "<RED>My text</RED>\n", 'The result was colorized and printed');


// @Test: printBox() prints text in a box with a width of 80 characters

  // fixtures
  $paddedText = str_pad('My text', 80, ' ');
  $colorizer->colorize($paddedText, 'RED')->returns('<RED>'.$paddedText.'</RED>');
  $colorizer->replay();
  // test
  ob_start();
  $printer->printBox('My text', 'RED');
  $result = ob_get_clean();
  // assertions
  $t->is($result, '<RED>'.$paddedText."</RED>\n", 'The result was colorized and printed');


// @Test: printLargeBox() prints text in a large box with a width of 80 characters or more

  // fixtures
  $paddedText = str_pad('  My text', 80, ' ');
  $paddedSpace = str_repeat(' ', 80);
  $colorizer->colorize($paddedText, 'RED')->returns('<RED>'.$paddedText.'</RED>');
  $colorizer->colorize($paddedSpace, 'RED')->returns('<RED>'.$paddedSpace.'</RED>');
  $colorizer->replay();
  // test
  ob_start();
  $printer->printLargeBox('My text', 'RED');
  $result = ob_get_clean();
  // assertions
  $t->is($result, '<RED>'.$paddedSpace."</RED>\n<RED>".$paddedText."</RED>\n<RED>".$paddedSpace."</RED>\n", 'The result was colorized and printed');


// @Test: The printer does also work without colorizer

  // fixtures
  $printer = new LimePrinter();
  // test
  ob_start();
  $printer->printText('My text');
  $result = ob_get_clean();
  // assertions
  $t->is($result, 'My text', 'The result was printed');


// @Test: strings in unformatted text are automatically formatted

  // fixtures
  $colorizer->colorize('Test string', LimePrinter::STRING)->returns('<BLUE>Test string</BLUE>');
  $colorizer->replay();
  // test
  ob_start();
  $printer->printText('My text with a "Test string"');
  $result = ob_get_clean();
  // assertions
  $t->is($result, 'My text with a <BLUE>Test string</BLUE>', 'The result was colorized and printed');


// @Test: functions in unformatted text are automatically formatted

  // @Test: Case 1 - Function without prefix

  // fixtures
  $colorizer->colorize('function(1, 2)', LimePrinter::METHOD)->returns('<BLUE>function(1, 2)</BLUE>');
  $colorizer->replay();
  // test
  ob_start();
  $printer->printText('My text with a function(1, 2)');
  $result = ob_get_clean();
  // assertions
  $t->is($result, 'My text with a <BLUE>function(1, 2)</BLUE>', 'The result was colorized and printed');

  // @Test: Case 2 - Function with "->" prefix

  // fixtures
  $colorizer->colorize('->function()', LimePrinter::METHOD)->returns('<BLUE>->function()</BLUE>');
  $colorizer->replay();
  // test
  ob_start();
  $printer->printText('My text with a ->function()');
  $result = ob_get_clean();
  // assertions
  $t->is($result, 'My text with a <BLUE>->function()</BLUE>', 'The result was colorized and printed');

  // @Test: Case 3 - Function with "::" prefix

  // fixtures
  $colorizer->colorize('::function(1, 2)', LimePrinter::METHOD)->returns('<BLUE>::function(1, 2)</BLUE>');
  $colorizer->replay();
  // test
  ob_start();
  $printer->printText('My text with a ::function(1, 2)');
  $result = ob_get_clean();
  // assertions
  $t->is($result, 'My text with a <BLUE>::function(1, 2)</BLUE>', 'The result was colorized and printed');
