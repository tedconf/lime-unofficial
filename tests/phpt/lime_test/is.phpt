--TEST--
is method
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->is(false, false);
?>
--EXPECT--
ok 1
1..1
# Looks like everything went fine.
