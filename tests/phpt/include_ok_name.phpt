--TEST--
include_ok method with test name
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->include_ok(dirname(__FILE__).'/setup.php', 'test name');
?>
--EXPECT--
ok 1 - test name
1..1
