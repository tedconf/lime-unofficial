--TEST--
isa method with test name
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
class Test {}
$t->isa(new Test(), 'Test', 'test name');
?>
--EXPECT--
ok 1 - test name
1..1
 Looks like everything went fine.
