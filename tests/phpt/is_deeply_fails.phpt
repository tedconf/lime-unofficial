--TEST--
is_deeply method that fails
--FILE--
<?php
require_once(dirname(__FILE__).'/setup.php');
$t->is_deeply(array(1, 2, array(1 => 'foo', 'a' => '4')), array(1, 2, array(1 => 'bar', 'a' => '4')));
?>
--EXPECT--
not ok 1
#     Failed test (./tests/phpt/is_deeply_fails.php at line 3)
#            got: array (  0 => 1,  1 => 2,  2 =>   array (    1 => 'foo',    'a' => '4',  ),)
#       expected: array (  0 => 1,  1 => 2,  2 =>   array (    1 => 'bar',    'a' => '4',  ),)
1..1
# Looks like you failed 1 tests of 1.
