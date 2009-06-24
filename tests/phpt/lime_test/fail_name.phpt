--TEST--
fail method with test name
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->fail('test name');
?>
--EXPECT--
not ok 1 - test name
#     Failed test (./tests/phpt/lime_test/fail_name.php at line 3)
1..1
 Looks like you failed 1 tests of 1.
