--TEST--
isDeeply method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->isDeeply(array(1, 2, array(1 => 'foo', 'a' => '4')), array(1, 2, array(1 => 'foo', 'a' => '4')));
?>
--EXPECT--
/test/unit/LimeTest/setup.php
ok 1
1..1
 Looks like everything went fine.
