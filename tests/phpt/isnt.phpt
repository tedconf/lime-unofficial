--TEST--
isnt method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->isnt(false, true);
?>
--EXPECT--
ok 1
1..1
