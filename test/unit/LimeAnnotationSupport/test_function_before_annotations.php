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

function testFunctionDefinition($param1, $param2 = null)
{
  function testNestedFunctionDefinition() {}
}

function testFunctionDefinitionInOneLine() {}

$t = new LimeTest(0);

// @Test
echo "Test\n";
