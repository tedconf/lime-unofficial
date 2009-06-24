--TEST--
ok method that fails
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->ok(0);
?>
--EXPECT--
not ok 1
#     Failed test (./tests/phpt/lime_test/ok_fails.php at line 3)
1..1
 Looks like you failed 1 tests of 1.
