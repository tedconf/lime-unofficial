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

define('FILE', dirname(__FILE__).'/test_resource.txt');

$t = new LimeTest(2);

// @BeforeAll

  if (file_exists(FILE)) unlink(FILE);
  file_put_contents(FILE, 'test');


// @AfterAll

  unlink(FILE);


// @Before

  $handle1 = fopen(FILE, 'r');
  $handle2 = fopen(FILE, 'r');


// @After

  $handle1 = null;
  $handle2 = null;


// @Test: assertEquals() throws an exception if the resources differ

  $actual = new LimeTesterResource($handle1);
  $expected = new LimeTesterResource($handle2);
  $t->expect('LimeTesterException');
  $actual->assertEquals($expected);


// @Test: assertEquals() throws no exception if the resources are the same

  $actual = new LimeTesterResource($handle1);
  $expected = new LimeTesterResource($handle1);
  $actual->assertEquals($expected);


// @Test: assertNotEquals() throws an exception if the resources are the same

  $actual = new LimeTesterResource($handle1);
  $expected = new LimeTesterResource($handle1);
  $t->expect('LimeTesterException');
  $actual->assertNotEquals($expected);


// @Test: assertNotEquals() throws no exception if the resources differ

  $actual = new LimeTesterResource($handle1);
  $expected = new LimeTesterResource($handle2);
  $actual->assertNotEquals($expected);