--TEST--
unlike method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->unlike('tests01', '/test\d+/');
?>
--EXPECT--
ok 1
1..1
