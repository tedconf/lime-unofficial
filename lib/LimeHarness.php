<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeHarness extends LimeRegistration
{
  protected
    $options    = array(),
    $executable = null,
    $stats      = array(),
    $output     = null;

  public function __construct(array $options = array())
  {
    $this->options = array_merge(array(
      'executable'   => null,
      'force_colors' => false,
      'output'       => null,
      'verbose'      => false,
    ), $options);

    $this->executable = self::findExecutable($this->options['executable']);
    $this->output = $this->options['output'] ? $this->options['output'] : new LimeOutput($this->options['force_colors']);
  }

  // TODO: This method should be moved to a seperate class
  public static function findExecutable($executable = null)
  {
    if (is_null($executable))
    {
      if (getenv('PHP_PATH'))
      {
        $executable = getenv('PHP_PATH');

        if (!is_executable($executable))
        {
          throw new Exception('The defined PHP_PATH environment variable is not a valid PHP executable.');
        }
      }
      else
      {
        $executable = PHP_BINDIR.DIRECTORY_SEPARATOR.'php';
      }
    }

    if (is_executable($executable))
    {
      return $executable;
    }

    $path = getenv('PATH') ? getenv('PATH') : getenv('Path');
    $extensions = DIRECTORY_SEPARATOR == '\\' ? (getenv('PATHEXT') ? explode(PATH_SEPARATOR, getenv('PATHEXT')) : array('.exe', '.bat', '.cmd', '.com')) : array('');
    foreach (array('php5', 'php') as $executable)
    {
      foreach ($extensions as $extension)
      {
        foreach (explode(PATH_SEPARATOR, $path) as $dir)
        {
          $file = $dir.DIRECTORY_SEPARATOR.$executable.$extension;
          if (is_executable($file))
          {
            return $file;
          }
        }
      }
    }

    throw new Exception("Unable to find PHP executable.");
  }

  public function toArray()
  {
    $results = array();
    foreach ($this->stats['files'] as $file => $stat)
    {
      $results = array_merge($results, $stat['output']);
    }

    return $results;
  }

  public function toXml()
  {
    return LimeTest::toXml($this->toArray());
  }

  public function run()
  {
    if (!count($this->files))
    {
      throw new Exception('You must register some test files before running them!');
    }

    // sort the files to be able to predict the order
    sort($this->files);

    $this->stats = array(
      'files'        => array(),
      'failed_files' => array(),
      'failed_tests' => 0,
      'total'        => 0,
    );

    foreach ($this->files as $file)
    {
      $this->stats['files'][$file] = array();
      $stats = &$this->stats['files'][$file];

      $relativeFile = $this->getRelativeFile($file);

      $testFile = tempnam(sys_get_temp_dir(), 'lime');
      $resultFile = tempnam(sys_get_temp_dir(), 'lime');
      file_put_contents($testFile, <<<EOF
<?php
include('$file');
file_put_contents('$resultFile', serialize(LimeTest::toArray()));
EOF
      );

      ob_start();
      // see http://trac.symfony-project.org/ticket/5437 for the explanation on the weird "cd" thing
      passthru(sprintf('cd & %s %s 2>&1', escapeshellarg($this->executable), escapeshellarg($testFile)), $return);
      ob_end_clean();
      unlink($testFile);

      $output = file_get_contents($resultFile);
      $stats['output'] = $output ? unserialize($output) : '';
      if (!$stats['output'])
      {
        $stats['output'] = array(array('file' => $file, 'tests' => array(), 'stats' => array('plan' => 1, 'total' => 1, 'failed' => array(0), 'passed' => array(), 'skipped' => array())));
      }
      unlink($resultFile);

      $fileStats = &$stats['output'][0]['stats'];

      $delta = 0;
      if ($return > 0)
      {
        $stats['status'] = 'dubious';
        $stats['status_code'] = $return;
      }
      else
      {
        $this->stats['total'] += $fileStats['total'];

        if (!$fileStats['plan'])
        {
          $fileStats['plan'] = $fileStats['total'];
        }

        $delta = $fileStats['plan'] - $fileStats['total'];
        if (0 != $delta)
        {
          $stats['status'] = 'dubious';
          $stats['status_code'] = 255;
        }
        else
        {
          $stats['status'] = $fileStats['failed'] ? 'not ok' : 'ok';
          $stats['status_code'] = 0;
        }
      }

      $this->output->echoln(sprintf('%s%s%s', substr($relativeFile, -min(67, strlen($relativeFile))), str_repeat('.', 70 - min(67, strlen($relativeFile))), $stats['status']));

      if (0 != $stats['status_code'])
      {
        $this->output->echoln(sprintf('    Test returned status %s', $stats['status_code']));
      }

      if ('ok' != $stats['status'])
      {
        $this->stats['failed_files'][] = $file;
      }

      if ($delta > 0)
      {
        $this->output->echoln(sprintf('    Looks like you planned %d tests but only ran %d.', $fileStats['plan'], $fileStats['total']));

        $this->stats['failed_tests'] += $delta;
        $this->stats['total'] += $delta;
      }
      else if ($delta < 0)
      {
        $this->output->echoln(sprintf('    Looks like you planned %s test but ran %s extra.', $fileStats['plan'], $fileStats['total'] - $fileStats['plan']));
      }

      if (false !== $fileStats && $fileStats['failed'])
      {
        $this->stats['failed_tests'] += count($fileStats['failed']);

        $this->output->echoln(sprintf("    Failed tests: %s", implode(', ', $fileStats['failed'])));
      }
    }

    if (count($this->stats['failed_files']))
    {
      $format = "%-30s  %4s  %5s  %5s  %s";
      $this->output->echoln(sprintf($format, 'Failed Test', 'Stat', 'Total', 'Fail', 'List of Failed'));
      $this->output->echoln("------------------------------------------------------------------");
      foreach ($this->stats['files'] as $file => $stat)
      {
        if (!in_array($file, $this->stats['failed_files']))
        {
          continue;
        }
        $relativeFile = $this->getRelativeFile($file);

        if (isset($stat['output'][0]))
        {
          $this->output->echoln(sprintf($format, substr($relativeFile, -min(30, strlen($relativeFile))), $stat['status_code'], count($stat['output'][0]['stats']['failed']) + count($stat['output'][0]['stats']['passed']), count($stat['output'][0]['stats']['failed']), implode(' ', $stat['output'][0]['stats']['failed'])));
        }
        else
        {
          $this->output->echoln(sprintf($format, substr($relativeFile, -min(30, strlen($relativeFile))), $stat['status_code'], '', '', ''));
        }
      }

      $this->output->redBar(sprintf('Failed %d/%d test scripts, %.2f%% okay. %d/%d subtests failed, %.2f%% okay.',
        $nbFailedFiles = count($this->stats['failed_files']),
        $nbFiles = count($this->files),
        ($nbFiles - $nbFailedFiles) * 100 / $nbFiles,
        $nbFailedTests = $this->stats['failed_tests'],
        $nbTests = $this->stats['total'],
        $nbTests > 0 ? ($nbTests - $nbFailedTests) * 100 / $nbTests : 0
      ));

      if ($this->options['verbose'])
      {
        foreach ($this->toArray() as $testSuite)
        {
          $first = true;
          foreach ($testSuite['stats']['failed'] as $testCase)
          {
            if (!isset($testSuite['tests'][$testCase]['file']))
            {
              continue;
            }

            if ($first)
            {
              $this->output->echoln('');
              $this->output->error($testSuite['file']);
              $first = false;
            }

            $this->output->comment(sprintf('  at %s line %s', $testSuite['tests'][$testCase]['file'], $testSuite['tests'][$testCase]['line']));
            $this->output->info('  '.$testSuite['tests'][$testCase]['message']);
            $this->output->echoln($testSuite['tests'][$testCase]['error'], null, false);
          }
        }
      }
    }
    else
    {
      $this->output->greenBar(' All tests successful.');
      $this->output->greenBar(sprintf(' Files=%d, Tests=%d', count($this->files), $this->stats['total']));
    }

    return $this->stats['failed_files'] ? false : true;
  }

  public function getFailedFiles()
  {
    return isset($this->stats['failed_files']) ? $this->stats['failed_files'] : array();
  }
}