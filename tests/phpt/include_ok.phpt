--TEST--
include_ok method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->include_ok(dirname(__FILE__).'/setup.php');
?>
--EXPECT--
ok 1
1..1
