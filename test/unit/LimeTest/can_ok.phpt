--TEST--
hasMethod method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
class Test { function test() {} }
$t->hasMethod(new Test(), 'test');
?>
--EXPECT--
/test/unit/LimeTest/setup.php
ok 1
1..1
 Looks like everything went fine.
