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

$t = new LimeTest(6);


// @Before

  $file = tempnam(sys_get_temp_dir(), 'lime');
  $output = LimeMock::create('LimeOutputInterface', $t);
  $connector = new LimeOutputRawConnector($output);


// @After

  $file = null;
  $output = null;
  $connector = null;


// @Test: The call to plan() is passed

  // fixtures
  $output->plan(1, '/test/file');
  $output->replay();
  file_put_contents($file, <<<EOF
<?php
echo serialize(array("plan", array(1, "/test/file")))."\n";
EOF
  );
  // test
  $connector->connect($file);
  // assertions
  $output->verify();


// @Test: The call to pass() is passed

  // fixtures
  $output->pass('A passed test', '/test/file', 11);
  $output->replay();
  file_put_contents($file, <<<EOF
<?php
echo serialize(array("pass", array("A passed test", "/test/file", 11)))."\n";
EOF
  );
  // test
  $connector->connect($file);
  // assertions
  $output->verify();


// @Test: The call to flush() is NOT passed

  // fixtures
  $output->flush()->never();
  $output->replay();
  file_put_contents($file, <<<EOF
<?php
echo serialize(array("flush", array()))."\n";
EOF
  );
  // test
  $connector->connect($file);
  // assertions
  $output->verify();


// @Test: Two arrays are converted to two method calls

  // fixtures
  $output->pass('A passed test', '/test/file', 11);
  $output->pass('Another passed test', '/test/file', 11);
  $output->replay();
  file_put_contents($file, <<<EOF
<?php
echo serialize(array("pass", array("A passed test", "/test/file", 11)))."\n";
echo serialize(array("pass", array("Another passed test", "/test/file", 11)))."\n";
EOF
  );
  // test
  $connector->connect($file);
  // assertions
  $output->verify();


// @Test: If the output cannot be unserialized, an exception is thrown

  // fixtures
  file_put_contents($file, '<?php echo "Some Error occurred";');
  // test
  $t->expect('RuntimeException');
  $connector->connect($file);

