--TEST--
include_ok method that fails
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->include_ok('foo.php');
?>
--EXPECT--
not ok 1
#     Failed test (./tests/phpt/include_ok_fails.php at line 3)
#       Tried to include 'foo.php'
1..1
 Looks like you failed 1 tests of 1.
