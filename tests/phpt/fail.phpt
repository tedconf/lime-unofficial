--TEST--
fail method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->fail();
?>
--EXPECT--
not ok 1
#     Failed test (./tests/phpt/fail.php at line 3)
1..1
 Looks like you failed 1 tests of 1.
