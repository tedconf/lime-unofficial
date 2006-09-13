--TEST--
todo method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->todo();
?>
--EXPECT--
ok 1 # TODO
1..1
