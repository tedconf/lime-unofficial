<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

include dirname(__FILE__).'/../bootstrap/unit.php';


class LimeAnnotationSupportTest extends LimeTest
{
  public function isOutput($actual, $expected, $method='is')
  {
    $this->$method(trim($actual), trim($expected), 'The test file returns the expected output');
  }
}


$t = new LimeAnnotationSupportTest(25);

$cli = LimeHarness::findExecutable().' ';


function execute($file)
{
  global $cli;

  $file = dirname(__FILE__).'/LimeAnnotationSupport/'.$file;

  // move the original file away to make sure it is not modified
  rename($file, $file.'.test.copy');
  copy($file.'.test.copy', $file);

  ob_start();
  passthru($cli.' '.$file, $result);
  $content = ob_get_clean();

  // move the original file back
  unlink($file);
  rename($file.'.test.copy', $file);

  return array($result, $content);
}


$t->diag('Code annotated with @Before is executed once before every test');

  // test
  list($result, $actual) = execute('test_before.php');
  // assertion
  $expected = <<<EOF
1..0
Before
Test 1
Before
Test 2
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->isOutput($actual, $expected);


$t->diag('Code annotated with @After is executed once after every test');

  // test
  list($result, $actual) = execute('test_after.php');
  // assertion
  $expected = <<<EOF
1..0
Test 1
After
Test 2
After
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->isOutput($actual, $expected);


$t->diag('Code annotated with @BeforeAll is executed once before the test suite');

  // test
  list($result, $actual) = execute('test_before_all.php');
  // assertion
  $expected = <<<EOF
1..0
Before All
Test 1
Test 2
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->isOutput($actual, $expected);


$t->diag('Code annotated with @AfterAll is executed once after the test suite');

  // test
  list($result, $actual) = execute('test_after_all.php');
  // assertion
  $expected = <<<EOF
1..0
Test 1
Test 2
After All
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->isOutput($actual, $expected);


$t->diag('Code before the first annotations is executed normally');

  // test
  list($result, $actual) = execute('test_code_before_annotations.php');
  // assertion
  $expected = <<<EOF
1..0
Before annotation
Before
Test
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->isOutput($actual, $expected);


$t->diag('Classes can be defined before the annotations');

  // test
  list($result, $actual) = execute('test_class_before_annotations.php');
  // assertion
  $expected = <<<EOF
1..0
Try is not matched
If is not matched
 Looks like everything went fine.
EOF
;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->isOutput($actual, $expected);


$t->diag('Functions can be defined before the annotations');

  // test
  list($result, $actual) = execute('test_function_before_annotations.php');
  // assertion
  $expected = <<<EOF
1..0
Test
 Looks like everything went fine.
EOF
;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->isOutput($actual, $expected);


$t->diag('Unknown annotations result in exceptions');

  // test
  list($result, $actual) = execute('test_ignore_unknown.php');
  // assertion
  $t->is($result, 255, 'The file returned exit status 255 (dubious)');


$t->diag('Variables from the @Before scope are available in all other scopes');

  // test
  list($result, $actual) = execute('test_scope_before.php');
  // assertion
  $expected = <<<EOF
1..0
Before
BeforeTest
BeforeTestAfter
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->isOutput($actual, $expected);


$t->diag('Variables from the global scope are available in all other scopes');

  // test
  list($result, $actual) = execute('test_scope_global.php');
  // assertion
  $expected = <<<EOF
1..0
Global
GlobalBefore
GlobalBeforeTest
GlobalBeforeTestAfter
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->isOutput($actual, $expected);


$t->diag('Tests annotated with @Test may have comments');

  // test
  list($result, $actual) = execute('test_comments.php');
  // assertion
  $expected = <<<EOF
1..0
Test 1
# This test is commented
Test 2
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->isOutput($actual, $expected);


$t->diag('Exceptions can be expected');

  // test
  list($result, $actual) = execute('test_expect.php');
  // assertion
  $expected = '/'.str_replace(array('%ANY%', '%EXCEPTION%'), array('.*', '"?RuntimeException"?'), preg_quote(<<<EOF
1..2
Test 1
not ok 1 - A %EXCEPTION% was thrown
#     Failed test (%ANY%)
#            got: NULL
#       expected: 'RuntimeException'
Test 2
ok 2 - A %EXCEPTION% was thrown
 Looks like you failed 1 tests of 2.
EOF
)).'/';
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->isOutput($actual, $expected, 'like');


$t->diag('Annotations can be commented out with /*...*/');

  // test
  list($result, $actual) = execute('test_multiline_comments.php');
  // assertion
  $expected = <<<EOF
1..0
Test 1
Test 3
 Looks like everything went fine.
EOF;
  $t->is($result, 0, 'The file returned exit status 0 (success)');
  $t->isOutput($actual, $expected);