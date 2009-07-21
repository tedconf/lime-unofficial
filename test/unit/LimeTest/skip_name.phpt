--TEST--
skip method with test name
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->skip('test name', 2);
?>
--EXPECT--
/test/unit/LimeTest/setup.php
skip 1 - test name
skip 2 - test name
1..2
 Looks like everything went fine.
