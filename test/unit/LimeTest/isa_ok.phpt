--TEST--
instanceOf method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
class Test {}
$t->instanceOf(new Test(), 'Test');
?>
--EXPECT--
ok 1
1..1
 Looks like everything went fine.
