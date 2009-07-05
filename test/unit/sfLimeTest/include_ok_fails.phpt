--TEST--
includeOk method that fails
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->includeOk('foo.php');
?>
--EXPECTF--
not ok 1
#     Failed test (%s/include_ok_fails.php at line 3)
#       Tried to include 'foo.php'
1..1
 Looks like you failed 1 tests of 1.
