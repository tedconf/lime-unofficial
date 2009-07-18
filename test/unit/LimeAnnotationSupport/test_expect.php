<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

include dirname(__FILE__).'/../../bootstrap/unit.php';

LimeAnnotationSupport::enable();

$t = new LimeTest(4);

// @Test
$t->expect('RuntimeException');
echo "Test 1\n";

// @Test
$t->expect('RuntimeException');
echo "Test 2\n";
throw new RuntimeException("Foobar");

// @Test
$t->expect('RuntimeException', 1);
echo "Test 3\n";
throw new RuntimeException("Foobar", 0);

// @Test
$t->expect('RuntimeException', 1);
echo "Test 4\n";
throw new RuntimeException("Foobar", 1);