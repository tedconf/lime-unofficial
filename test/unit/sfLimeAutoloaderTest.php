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

$t = new sfLimeTest(5);


$t->diag('->autoload() loads class files by class name');

$autoloader = new sfLimeAutoloader();
$t->is($autoloader->autoload('sfLimeCoverage'), true, 'Returns true if a class can be loaded');
$t->is($autoloader->autoload('Foo'), false, 'Does not load classes that do not begin with "sfLime"');
$t->is($autoloader->autoload('Foo'), false, 'Does not load classes that do not begin with "sfLime"');


$t->diag('->autoload() loads old class names if legacy mode is enabled');

$t->is($autoloader->autoload('lime_test'), false, 'Does not load old classes in normal mode');
sfLimeAutoloader::enableLegacyMode();
$t->is($autoloader->autoload('lime_test'), true, 'Loads old classes in legacy mode');