<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeCoverage extends LimeRegistration
{
  protected
    $files      = array(),
    $extension  = '.php',
    $baseDir    = '',
    $harness    = null,
    $verbose    = false,
    $coverage   = array();

  public function __construct(LimeHarness $harness)
  {
    $this->harness = $harness;

    if (!function_exists('xdebug_start_code_coverage'))
    {
      throw new Exception('You must install and enable xdebug before using lime coverage.');
    }

    if (!ini_get('xdebug.extended_info'))
    {
      throw new Exception('You must set xdebug.extended_info to 1 in your php.ini to use lime coverage.');
    }
  }
    
  public function setFiles($files)
  {
    if (!is_array($files))
    {
      $files = array($files);
    }
    
  	$this->files = $files;
  }
  
  public function setExtension($extension)
  {
  	$this->extension = $extension;
  }
  
  public function setBaseDir($baseDir)
  {
  	$this->baseDir = $baseDir;
  }
  
  public function setVerbose($verbose)
  {
  	$this->verbose = $verbose;
  }

  public function run()
  {
    if (!count($this->harness->files))
    {
      throw new Exception('You must register some test files before running coverage!');
    }

    if (!count($this->files))
    {
      throw new Exception('You must register some files to cover!');
    }

    $this->coverage = array();

    $this->process($this->harness->files);

    $this->output($this->files);
  }

  protected function process(array $files)
  {
    $tmpFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'test.php';
    foreach ($files as $file)
    {
      $tmp = <<<EOF
<?php
xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
include('$file');
echo '<PHP_SER>'.serialize(xdebug_get_code_coverage()).'</PHP_SER>';
EOF;
      file_put_contents($tmpFile, $tmp);
      ob_start();
      // see http://trac.symfony-project.org/ticket/5437 for the explanation on the weird "cd" thing
      passthru(sprintf('cd & %s %s 2>&1', escapeshellarg($this->harness->executable), escapeshellarg($tmpFile)), $return);
      $retval = ob_get_clean();

      if (0 != $return) // test exited without success
      {
        // something may have gone wrong, we should warn the user so they know
        // it's a bug in their code and not symfony's

        // TODO: can this line be replaced by a call to ->error()?
        $this->harness->output->echoln(sprintf('Warning: %s returned status %d, results may be inaccurate', $file, $return), LimeOutput::ERROR);
      }

      if (false === $cov = @unserialize(substr($retval, strpos($retval, '<PHP_SER>') + 9, strpos($retval, '</PHP_SER>') - 9)))
      {
        if (0 == $return)
        {
          // failed to serialize, but PHP said it should of worked.
          // something is seriously wrong, so abort with exception
          throw new Exception(sprintf('Unable to unserialize coverage for file "%s"', $file));
        }
        else
        {
          // failed to serialize, but PHP warned us that this might have happened.
          // so we should ignore and move on
          continue; // continue foreach loop through $this->harness->files
        }
      }

      foreach ($cov as $file => $lines)
      {
        if (!isset($this->coverage[$file]))
        {
          $this->coverage[$file] = $lines;
          continue;
        }

        foreach ($lines as $line => $flag)
        {
          if ($flag == 1)
          {
            $this->coverage[$file][$line] = 1;
          }
        }
      }
    }

    if (file_exists($tmpFile))
    {
      unlink($tmpFile);
    }
  }

  protected function output(array $files)
  {
    ksort($this->coverage);
    $totalPhpLines = 0;
    $totalCoveredLines = 0;
    foreach ($files as $file)
    {
      $file = realpath($file);
      $isCovered = isset($this->coverage[$file]);
      $cov = isset($this->coverage[$file]) ? $this->coverage[$file] : array();
      $coveredLines = array();
      $missingLines = array();

      foreach ($cov as $line => $flag)
      {
        switch ($flag)
        {
          case 1:
            $coveredLines[] = $line;
            break;
          case -1:
            $missingLines[] = $line;
            break;
        }
      }

      $totalLines = count($coveredLines) + count($missingLines);
      if (!$totalLines)
      {
        // probably means that the file is not covered at all!
        $totalLines = count($this->getPhpLines(file_get_contents($file)));
      }

      $output = $this->harness->output;
      $percent = $totalLines ? count($coveredLines) * 100 / $totalLines : 0;

      $totalPhpLines += $totalLines;
      $totalCoveredLines += count($coveredLines);

      $relativeFile = $this->getRelativeFile($file);
      $output->echoln(sprintf("%-70s %3.0f%%", substr($relativeFile, -min(70, strlen($relativeFile))), $percent), $percent == 100 ? LimeOutput::INFO : ($percent > 90 ? LimeOutput::PARAMETER : ($percent < 20 ? LimeOutput::ERROR : '')));
      if ($this->verbose && $isCovered && $percent != 100)
      {
        $output->comment(sprintf("missing: %s", $this->formatRange($missingLines)));
      }
    }

    $output->echoln(sprintf("TOTAL COVERAGE: %3.0f%%", $totalPhpLines ? $totalCoveredLines * 100 / $totalPhpLines : 0));
  }

  protected static function getPhpLines($content)
  {
    if (is_readable($content))
    {
      $content = file_get_contents($content);
    }

    $tokens = token_get_all($content);
    $phpLines = array();
    $currentLine = 1;
    $inClass = false;
    $inFunction = false;
    $inFunctionDeclaration = false;
    $endOfCurrentExpr = true;
    $openBraces = 0;
    foreach ($tokens as $token)
    {
      if (is_string($token))
      {
        switch ($token)
        {
          case '=':
            if (false === $inClass || (false !== $inFunction && !$inFunctionDeclaration))
            {
              $phpLines[$currentLine] = true;
            }
            break;
          case '{':
            ++$openBraces;
            $inFunctionDeclaration = false;
            break;
          case ';':
            $inFunctionDeclaration = false;
            $endOfCurrentExpr = true;
            break;
          case '}':
            $endOfCurrentExpr = true;
            --$openBraces;
            if ($openBraces == $inClass)
            {
              $inClass = false;
            }
            if ($openBraces == $inFunction)
            {
              $inFunction = false;
            }
            break;
        }

        continue;
      }

      list($id, $text) = $token;

      switch ($id)
      {
        case T_CURLY_OPEN:
        case T_DOLLAR_OPEN_CURLY_BRACES:
          ++$openBraces;
          break;
        case T_WHITESPACE:
        case T_OPEN_TAG:
        case T_CLOSE_TAG:
          $endOfCurrentExpr = true;
          $currentLine += count(explode("\n", $text)) - 1;
          break;
        case T_COMMENT:
        case T_DOC_COMMENT:
          $currentLine += count(explode("\n", $text)) - 1;
          break;
        case T_CLASS:
          $inClass = $openBraces;
          break;
        case T_FUNCTION:
          $inFunction = $openBraces;
          $inFunctionDeclaration = true;
          break;
        case T_AND_EQUAL:
        case T_BREAK:
        case T_CASE:
        case T_CATCH:
        case T_CLONE:
        case T_CONCAT_EQUAL:
        case T_CONTINUE:
        case T_DEC:
        case T_DECLARE:
        case T_DEFAULT:
        case T_DIV_EQUAL:
        case T_DO:
        case T_ECHO:
        case T_ELSEIF:
        case T_EMPTY:
        case T_ENDDECLARE:
        case T_ENDFOR:
        case T_ENDFOREACH:
        case T_ENDIF:
        case T_ENDSWITCH:
        case T_ENDWHILE:
        case T_EVAL:
        case T_EXIT:
        case T_FOR:
        case T_FOREACH:
        case T_GLOBAL:
        case T_IF:
        case T_INC:
        case T_INCLUDE:
        case T_INCLUDE_ONCE:
        case T_INSTANCEOF:
        case T_ISSET:
        case T_IS_EQUAL:
        case T_IS_GREATER_OR_EQUAL:
        case T_IS_IDENTICAL:
        case T_IS_NOT_EQUAL:
        case T_IS_NOT_IDENTICAL:
        case T_IS_SMALLER_OR_EQUAL:
        case T_LIST:
        case T_LOGICAL_AND:
        case T_LOGICAL_OR:
        case T_LOGICAL_XOR:
        case T_MINUS_EQUAL:
        case T_MOD_EQUAL:
        case T_MUL_EQUAL:
        case T_NEW:
        case T_OBJECT_OPERATOR:
        case T_OR_EQUAL:
        case T_PLUS_EQUAL:
        case T_PRINT:
        case T_REQUIRE:
        case T_REQUIRE_ONCE:
        case T_RETURN:
        case T_SL:
        case T_SL_EQUAL:
        case T_SR:
        case T_SR_EQUAL:
        case T_SWITCH:
        case T_THROW:
        case T_TRY:
        case T_UNSET:
        case T_UNSET_CAST:
        case T_USE:
        case T_WHILE:
        case T_XOR_EQUAL:
          $phpLines[$currentLine] = true;
          $endOfCurrentExpr = false;
          break;
        default:
          if (false === $endOfCurrentExpr)
          {
            $phpLines[$currentLine] = true;
          }
      }
    }

    return $phpLines;
  }

  public function compute($content, array $cov)
  {
    $phpLines = self::getPhpLines($content);

    // we remove from $cov non php lines
    foreach (array_diff_key($cov, $phpLines) as $line => $tmp)
    {
      unset($cov[$line]);
    }

    return array($cov, $phpLines);
  }

  protected function formatRange(array $lines)
  {
    sort($lines);
    $formatted = '';
    $first = -1;
    $last = -1;
    foreach ($lines as $line)
    {
      if ($last + 1 != $line)
      {
        if ($first != -1)
        {
          $formatted .= $first == $last ? "$first " : "[$first - $last] ";
        }
        $first = $line;
        $last = $line;
      }
      else
      {
        $last = $line;
      }
    }
    if ($first != -1)
    {
      $formatted .= $first == $last ? "$first " : "[$first - $last] ";
    }

    return $formatted;
  }
}