<?php

include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new LimeTest(4);


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