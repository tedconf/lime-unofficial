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

$t = new LimeTest(12);


// @Before

  $file = tempnam(sys_get_temp_dir(), 'lime');
  $output = LimeMock::create('LimeOutputInterface', $t);
  $connector = new LimeOutputPipe($output);


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


// @Test: The file is called with the argument --raw

  // fixtures
  $output->plan(1, '/test/file');
  $output->replay();
  file_put_contents($file, <<<EOF
<?php
if (in_array('--raw', \$GLOBALS['argv']))
{
  echo serialize(array("plan", array(1, "/test/file")))."\n";
}
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


// @Test: Method calls can be suppressed by passing the first constructor parameter

  // fixtures
  $output->invoke('pass')->never();
  $output->replay();
  $connector = new LimeOutputPipe($output, array('pass'));
  file_put_contents($file, <<<EOF
<?php
echo serialize(array("pass", array("A passed test", "/test/file", 11)))."\n";
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


// @Test: A split serialized array can be read correctly

  // fixtures
  $output->pass('A passed test', '/test/file', 11);
  $output->replay();
  file_put_contents($file, <<<EOF
<?php
\$serialized = serialize(array("pass", array("A passed test", "/test/file", 11)))."\n";
\$strings =  str_split(\$serialized, strlen(\$serialized)/2 + 1);
echo \$strings[0];
echo \$strings[1];
EOF
  );
  // test
  $connector->connect($file);
  // assertions
  $output->verify();


// @Test: Escaped arguments are unescaped

  // fixtures
  $output->comment("A \\n\\r comment \n with line \r breaks");
  $output->replay();
  // don't use <<<EOF here because of escaping
  // escape string again, because all backslashes (\) must be escaped in
  // single quoted strings
  file_put_contents($file, addcslashes('<?php echo serialize(array("comment", array("A \\\\n\\\\r comment \\n with line \\r breaks")))."\n";', '\\'));
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


// @Test: A PHP error is passed to error() - invalid identifier

  // @Test: Case 1 - Invalid identifier

  // fixtures
  $output->error("Parse error: syntax error, unexpected T_LNUMBER, expecting T_VARIABLE or '$'", $file, 1);
  $output->replay();
  file_put_contents($file, '<?php $1invalidname;');
  // test
  $connector->connect($file);
  // assertions
  $output->verify();


  // @Test: Case 2 - Failed require

  // fixtures
  $output->warning("Warning: require(foobar.php): failed to open stream: No such file or directory", $file, 1);
  $output->error("Fatal error: require(): Failed opening required 'foobar.php' (include_path='".get_include_path()."')", $file, 1);
  $output->replay();
  file_put_contents($file, '<?php require "foobar.php";');
  // test
  $connector->connect($file);
  // assertions
  $output->verify();