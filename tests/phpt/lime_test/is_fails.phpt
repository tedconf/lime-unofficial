--TEST--
is method that fails
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->is(false, true);
?>
--EXPECT--
not ok 1
#     Failed test (./tests/phpt/lime_test/is_fails.php at line 3)
#            got: false
#       expected: true
1..1
 Looks like you failed 1 tests of 1.
