--TEST--
includeOk method with test name
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->includeOk(dirname(__FILE__).'/include_test.php', 'test name');
?>
--EXPECT--
ok 1 - test name
1..1
 Looks like everything went fine.
