<?php

/*
 * This file is part of the Lime framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Bernhard Schussek <bernhard.schussek@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

include dirname(__FILE__).'/../bootstrap/unit.php';

LimeAnnotationSupport::enable();

$t = new LimeTest(3);


// @BeforeAll

  $executable = $t->stub('LimeExecutable');


// @Before

  $label1 = new LimeLabel();
  $label1->addFile(new LimeFile('test1.txt', $executable));
  $label1->addFile(new LimeFile('test2.txt', $executable));
  $label2 = new LimeLabel();
  $label2->addFile(new LimeFile('test1.txt', $executable));
  $label2->addFile(new LimeFile('test3.txt', $executable));


// @Test: intersect() returns the intersection of two labels

  $expected = new LimeLabel();
  $expected->addFile(new LimeFile('test1.txt', $executable));
  $actual = $label1->intersect($label2);
  $t->is($actual, $expected, 'The intersection is correct');


// @Test: add() returns the sum of two labels

  $expected = new LimeLabel();
  $expected->addFile(new LimeFile('test1.txt', $executable));
  $expected->addFile(new LimeFile('test2.txt', $executable));
  $expected->addFile(new LimeFile('test3.txt', $executable));
  $actual = $label1->add($label2);
  $t->is($actual, $expected, 'The sum is correct');


// @Test: subtract() returns the first label without the second

  $expected = new LimeLabel();
  $expected->addFile(new LimeFile('test2.txt', $executable));
  $actual = $label1->subtract($label2);
  $t->is($actual, $expected, 'The subtraction is correct');