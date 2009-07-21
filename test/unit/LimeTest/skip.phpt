--TEST--
skip method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->skip();
?>
--EXPECT--
/test/unit/LimeTest/setup.php
skip 1
1..1
 Looks like everything went fine.
