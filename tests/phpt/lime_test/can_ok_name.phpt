--TEST--
hasMethod method with test name
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
class Test { function test() {} }
$t->hasMethod(new Test(), 'test', 'test name');
?>
--EXPECT--
ok 1 - test name
1..1
 Looks like everything went fine.
