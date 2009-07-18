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


$t = new LimeTest(2);


$t->diag('All variables are extracted from the text');

  // fixtures
  $l = new LimeLexerVariables();
  // test
  $actual = $l->parse(<<<EOF
<?php
\$a = 0;
\$b = 1;
EOF
);
  // assertions
  $expected = array('$a', '$b');
  $t->is($actual, $expected, 'The correct variables are returned');


$t->diag('Variables in classes and functions are ignored');

  // fixtures
  $l = new LimeLexerVariables();
  // test
  $actual = $l->parse(<<<EOF
<?php
\$a = 0;
function foo()
{
  \$b = 1;
}
class bar
{
  protected \$c = 2;
}
EOF
);
  // assertions
  $expected = array('$a');
  $t->is($actual, $expected, 'The correct variables are returned');