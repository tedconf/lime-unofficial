<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

abstract class LimeLexerAnnotationAware extends LimeLexer
{
  private
    $allowedAnnotations,
    $currentAnnotation,
    $currentAnnotationComment,
    $inAnnotation,
    $inAnnotationDeclaration;

  public function __construct(array $allowedAnnotations = array())
  {
    $this->allowedAnnotations = $allowedAnnotations;
  }

  public function parse($content)
  {
    $this->currentAnnotation = null;
    $this->currentAnnotationComment = null;
    $this->inAnnotation = false;
    $this->inAnnotationDeclaration = false;

    return parent::parse($content);
  }

  protected function beforeProcess($text, $id)
  {
    if (!$this->inClass() && !$this->inFunction() && $id = T_COMMENT && strpos($text, '//') === 0)
    {
      list($annotation, $comment) = $this->extractAnnotation($text);

      if (!is_null($annotation))
      {
        $this->currentAnnotation = $annotation;
        $this->currentAnnotationComment = $comment;
        $this->inAnnotation = true;
        $this->inAnnotationDeclaration = true;
      }
    }
    else
    {
      $this->inAnnotationDeclaration = false;
    }
  }

  protected function inAnnotation()
  {
    return $this->inAnnotation;
  }

  protected function inAnnotationDeclaration()
  {
    return $this->inAnnotationDeclaration;
  }

  protected function getCurrentAnnotation()
  {
    return $this->currentAnnotation;
  }

  protected function getCurrentAnnotationComment()
  {
    return $this->currentAnnotationComment;
  }

  protected function getAllowedAnnotations()
  {
    return $this->allowedAnnotations;
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