--TEST--
cmp_ok method with test name
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->cmp_ok(2, '>', 1, 'test name');
?>
--EXPECT--
ok 1 - test name
1..1
