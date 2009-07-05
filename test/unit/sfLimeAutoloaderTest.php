<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require_once dirname(__FILE__).'/../bootstrap/unit.php';

$t = new sfLimeTest(2);


$t->diag('->autoload() loads class files by class name');

$autoloader = new sfLimeAutoloader();
$t->is($autoloader->autoload('sfLimeCoverage'), true, 'Returns true if a class can be loaded');
$t->is($autoloader->autoload('Foo'), false, 'Does not load classes that do not begin with "sfLime"');

