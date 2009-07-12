<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Transforms annotated code in a file into functions.
 *
 * The created function names are returned by the function parse(), indexed
 * by annotation name.
 *
 * <code>
 * $lexer = new LimeLexerAnnotations('path/to/transformed/file.php', array('First', 'Second'));
 * $functions = $lexer->parse('/path/to/original/file.php');
 *
 * // => array('First' => array(...), 'Second' => array(...))
 * </code>
 *
 * The annotated source file for the above code could look like this:
 *
 * <code>
 * $test = 'nothing';
 *
 * // @First
 * $test = 'First';
 *
 * // @Second
 * $test = 'Second';
 *
 * // @First
 * echo $test;
 * </code>
 *
 * You can include the transformed file and execute a certain subset of
 * annotations:
 *
 * <code>
 * include 'path/to/transformed/file.php';
 *
 * foreach ($functions['First'] as $function)
 * {
 *   $function();
 * }
 *
 * // => First
 * </code>
 *
 * @package    lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class LimeLexerAnnotations extends LimeLexer
{
  protected
    $allowedAnnotations,
    $fileName,
    $file,
    $variables,
    $functions,
    $inAnnotation,
    $functionCount,
    $initialized;

  /**
   * Constructor.
   *
   * @param  string $targetFile          The file where the transformed code
   *                                     will be written.
   * @param  array  $allowedAnnotations  The allowed annotations.
   */
  public function __construct($targetFile, array $allowedAnnotations)
  {
    $this->fileName = $targetFile;
    $this->allowedAnnotations = $allowedAnnotations;
  }

  /**
   * Transforms the annoated code in the given file and writes it to the
   * target file.
   *
   * @see LimeLexer#parse($content)
   */
  public function parse($path)
  {
    if (!is_readable($path))
    {
      throw new InvalidArgumentException('The given file is not readable');
    }

    $lexer = new LimeLexerVariables();

    $this->path = $path;
    $this->inAnnotation = false;
    $this->initialized = false;
    $this->functionCount = 0;
    $this->variables = $lexer->parse($this->path);
    $this->functions = array();

    foreach ($this->allowedAnnotations as $annotation)
    {
      $this->functions[$annotation] = array();
    }

    // backup the contents for the case that the path == filename
    $content = file_get_contents($path);
    $this->file = fopen($this->fileName, 'w');

    $result = parent::parse($content);

    if ($this->inAnnotation)
    {
      fwrite($this->file, '}');
    }

    fclose($this->file);

    return $result;
  }

  /**
   * (non-PHPdoc)
   * @see LimeLexer#process($text, $id)
   */
  protected function process($text, $id)
  {
    if ($id == T_OPEN_TAG && !$this->initialized)
    {
      $text .= 'global '.implode(', ', $this->variables).';';
      $this->initialized = true;
    }
    else if ($this->inClass() || $this->inFunction())
    {
      $text = '';
    }
    else if ($id = T_COMMENT && strpos($text, '//') === 0)
    {
      list($annotation, $comment) = $this->extractAnnotation($text);

      if (!is_null($annotation))
      {
        $functionName = '__lime_annotation_'.($this->functionCount++);
        $this->functions[$annotation][] = $functionName;

        $text = $this->inAnnotation ? '} ' : '';
        $this->inAnnotation = true;
        $text .= sprintf('function %s() { global %s;', $functionName, implode(', ', $this->variables));

//        if (!empty($comment))
//        {
//          $line .= ' '.$this->testVariable.'->comment("'.$data.'");';
//        }
      }
    }

    fwrite($this->file, $text);
  }

  /**
   * (non-PHPdoc)
   * @see LimeLexer#getResult()
   */
  protected function getResult()
  {
    return $this->functions;
  }

  /**
   * Extracts an annotation from a single-line comment and validates it.
   *
   * Possible valid annotations are:
   * <code>
   * // @Annotation
   * // @Annotation: Some comment here
   * </code>
   *
   * The results for those annotations are:
   * <code>
   * array('Annotation', null);
   * array('Annotation', 'Some comment here');
   * </code>
   *
   * @param  string $text  Some code
   *
   * @return array         An array with the annotation name and the annotation
   *                       comment. If either of both cannot be read, it is NULL.
   */
  protected function extractAnnotation($text)
  {
    if (preg_match('/^\/\/\s*@(\w+)([:\s]+(.*))?\s*$/', $text, $matches))
    {
      $annotation = $matches[1];
      $data = count($matches) > 3 ? trim($matches[3]) : null;

      if (!in_array($annotation, $this->allowedAnnotations))
      {
        throw new LogicException(sprintf('The annotation "%s" is not valid', $annotation));
      }

      return array($annotation, $data);
    }
    else
    {
      return array(null, null);
    }
  }
}