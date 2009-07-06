<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require_once(dirname(__FILE__).'/../../lib/LimeAutoloader.php');

LimeAutoloader::register();

$h = new LimeHarness(array(
  'force_colors' => isset($argv) && in_array('--color', $argv),
  'verbose'      => isset($argv) && in_array('--verbose', $argv),
));
$h->base_dir = realpath(dirname(__FILE__).'/..');

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__FILE__).'/../unit'), RecursiveIteratorIterator::LEAVES_ONLY) as $file)
{
  if (preg_match('/Test\.php$/', $file))
  {
    $h->register($file->getRealPath());
  }
}

exit($h->run() ? 0 : 1);