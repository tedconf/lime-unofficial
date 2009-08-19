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

$t = new LimeTest(2);


// @Before

  $file = tempnam(sys_get_temp_dir(), 'lime');
  $output = LimeMock::create('LimeOutputInterface', $t);
  $analyzer = new LimeTestAnalyzer($output);


// @After

  $file = null;
  $output = null;
  $analyzer = null;


// @Test: The file is called with the argument --output=raw

  // fixtures
  $output->plan(2);
  $output->replay();
  file_put_contents($file, <<<EOF
<?php
if (in_array('--output=raw', \$GLOBALS['argv']))
{
  echo "1..2\n";
}
EOF
  );
  // test
  $analyzer->connect($file);
  while (!$analyzer->done()) $analyzer->proceed();
  // assertions
  $output->verify();


// @Test: If the output cannot be unserialized, an error is reported

  // fixtures
  file_put_contents($file, '<?php echo "\0raw\0Some Error occurred";');
  $output->warning("Could not parse test output. Make sure you don't echo any additional data.", $file, 1);
  $output->replay();
  // test
  $analyzer->connect($file);
  while (!$analyzer->done()) $analyzer->proceed();
  // assertions
  $output->verify();


