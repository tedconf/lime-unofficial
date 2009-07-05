--TEST--
includeOk method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->includeOk(dirname(__FILE__).'/include_test.php');
?>
--EXPECT--
ok 1
1..1
 Looks like everything went fine.
