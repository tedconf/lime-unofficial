--TEST--
pass method with test name
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->pass('test name');
?>
--EXPECT--
ok 1 - test name
1..1
