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

LimeAnnotationSupport::enable();

$t = new LimeTest(6);


// @Before

  $shell = new LimeShell();
  $file = tempnam(sys_get_temp_dir(), 'lime');


// @After

  $shell = null;
  $file = null;


// @Test: PHP code can be executed

  // test
  list($returnValue, $output) = $shell->execute(<<<EOF
echo "Test";
exit(1);
EOF
);
  // assertions
  $t->is($returnValue, 1, 'The return value is correct');
  $t->is($output, 'Test', 'The output is correct');


// @Test: PHP scripts can be executed

  // fixtures
  file_put_contents($file, <<<EOF
<?php
echo "Test";
exit(1);
EOF
);
  // test
  list($returnValue, $output) = $shell->execute($file);
  // assertions
  $t->is($returnValue, 1, 'The return value is correct');
  $t->is($output, 'Test', 'The output is correct');


// @Test: PHP scripts can be executed with arguments

  // fixtures
  file_put_contents($file, <<<EOF
<?php
unset(\$GLOBALS['argv'][0]);
var_export(\$GLOBALS['argv']);
exit(1);
EOF
);
  // test
  list($returnValue, $output) = $shell->execute($file, array('test' => true, 'arg' => 'value'));
  // assertions
  $t->is($returnValue, 1, 'The return value is correct');
  $t->is($output, "array (
  1 => '--test',
  2 => '--arg=value',
)", 'The output is correct');


