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

class TestClassDefinition
{
  public function testMethodDefinition()
  {
    function testNestedFunctionDefinition() {}

    // test whether $this is ignored
    $this->__toString();
  }
}

class TestClassDefinitionInOneLine {}

interface TestInterfaceDefinition {}

abstract class TestAbstractClassDefinition {}

class TestExtendingClassDefinition extends TestClassDefinition implements TestInterfaceDefinition {}

class TestImplementingClassDefinition implements TestInterfaceDefinition {}

$t = new LimeTest(0);

// @Test
try
{
  throw new Exception();
} catch (Exception $e)
{
  echo "Try is not matched\n";
}

// @Test
if (false)
{
}
else
{
  echo "If is not matched\n";
}
