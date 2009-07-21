--TEST--
compare method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->compare(2, '>', 1);
?>
--EXPECT--
/test/unit/LimeTest/setup.php
ok 1
1..1
 Looks like everything went fine.
