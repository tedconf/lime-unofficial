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

$t = new LimeTest(6);


$t->diag('PHP code can be executed');

  // fixtures
  $s = new LimeShell();
  // test
  list($returnValue, $output) = $s->execute(<<<EOF
echo "Test";
exit(1);
EOF
);
  // assertions
  $t->is($returnValue, 1, 'The return value is correct');
  $t->is($output, 'Test', 'The output is correct');


$t->diag('PHP scripts can be executed');

  // fixtures
  $s = new LimeShell();
  $file = tempnam(sys_get_temp_dir(), 'lime');
  file_put_contents($file, <<<EOF
<?php
echo "Test";
exit(1);
EOF
);
  // test
  list($returnValue, $output) = $s->execute($file);
  // assertions
  $t->is($returnValue, 1, 'The return value is correct');
  $t->is($output, 'Test', 'The output is correct');


$t->diag('PHP scripts can be executed with arguments');

  // fixtures
  $s = new LimeShell();
  $file = tempnam(sys_get_temp_dir(), 'lime');
  file_put_contents($file, <<<EOF
<?php
unset(\$GLOBALS['argv'][0]);
var_export(\$GLOBALS['argv']);
exit(1);
EOF
);
  // test
  list($returnValue, $output) = $s->execute($file, array('test', '--arg'));
  // assertions
  $t->is($returnValue, 1, 'The return value is correct');
  $t->is($output, "array (
  1 => 'test',
  2 => '--arg',
)", 'The output is correct');