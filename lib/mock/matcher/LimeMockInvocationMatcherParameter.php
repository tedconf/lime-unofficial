<?php

/*
 * This file is part of the Lime test framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Requires a method call to satisfy a constraint for one parameter.
 *
 * The index of the parameter is given to the constructor. The desired
 * constraint can be configured by calling any of the methods of this class.
 *
 * @package    Lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 * @see        LimeMockInvocationMatcherInterface
 */
class LimeMockInvocationMatcherParameter implements LimeMockInvocationMatcherInterface
{
  private
    $index      = null,
    $parent     = null,
    $constraint = null;

  /**
   * Constructor.
   *
   * @param integer $index
   * @param LimeMockInvocationExpectation $parent
   */
  public function __construct($index, LimeMockInvocationExpectation $parent)
  {
    $this->index = $index;
    $this->parent = $parent;
  }

  /**
   * (non-PHPdoc)
   * @see mock/matcher/LimeMockInvocationMatcherInterface#invoke($invocation)
   */
  public function invoke(LimeMockInvocation $invocation)
  {
    try
    {
      if (!is_null($this->constraint))
      {
        $this->constraint->evaluate($invocation->getParameter($this->index-1));
      }
    }
    catch (LimeConstraintException $e)
    {
      throw new LimeMockInvocationMatcherException("was called with wrong parameter $this->index\n".$e->getMessage());
    }
    catch (OutOfRangeException $e)
    {
      throw new LimeMockInvocationMatcherException("was not called with $this->index or more parameters");
    }
  }

  /**
   * (non-PHPdoc)
   * @see mock/matcher/LimeMockInvocationMatcherInterface#isInvokable()
   */
  public function isInvokable()
  {
    return true;
  }

  /**
   * (non-PHPdoc)
   * @see mock/matcher/LimeMockInvocationMatcherInterface#isSatisfied()
   */
  public function isSatisfied()
  {
    return true;
  }

  /**
   * (non-PHPdoc)
   * @see mock/matcher/LimeMockInvocationMatcherInterface#getMessage()
   */
  public function getMessage()
  {
    return '';
  }

  /**
   * Sets the constraint and returns the related invocation expectation object.
   *
   * @param  LimeConstraintInterface $constraint
   * @return LimeMockInvocationExpectation
   */
  private function setConstraint(LimeConstraintInterface $constraint)
  {
    $this->constraint = $constraint;

    return $this->parent;
  }

  /**
   * Requires the parameter to be equal to the given value.
   *
   * @param  LimeConstraintInterface $constraint
   * @return LimeMockInvocationExpectation
   * @see    LimeConstraintIs
   */
  public function is($expected)
  {
    return $this->setConstraint(new LimeConstraintIs($expected));
  }

  /**
   * Requires the parameter to be not equal to the given value.
   *
   * @param  LimeConstraintInterface $constraint
   * @return LimeMockInvocationExpectation
   * @see    LimeConstraintIsNot
   */
  public function isnt($expected)
  {
    return $this->setConstraint(new LimeConstraintIsNot($expected));
  }

  /**
   * Requires the parameter to be identical to the given value.
   *
   * @param  LimeConstraintInterface $constraint
   * @return LimeMockInvocationExpectation
   * @see    LimeConstraintSame
   */
  public function same($expected)
  {
    return $this->setConstraint(new LimeConstraintSame($expected));
  }

  /**
   * Requires the parameter to be not identical to the given value.
   *
   * @param  LimeConstraintInterface $constraint
   * @return LimeMockInvocationExpectation
   * @see    LimeConstraintNotSame
   */
  public function notSame($expected)
  {
    return $this->setConstraint(new LimeConstraintNotSame($expected));
  }

  /**
   * Requires the parameter to be like the given value.
   *
   * @param  LimeConstraintInterface $constraint
   * @return LimeMockInvocationExpectation
   * @see    LimeConstraintLike
   */
  public function like($expected)
  {
    return $this->setConstraint(new LimeConstraintLike($expected));
  }

  /**
   * Requires the parameter to be unlike the given value.
   *
   * @param  LimeConstraintInterface $constraint
   * @return LimeMockInvocationExpectation
   * @see    LimeConstraintUnlike
   */
  public function unlike($expected)
  {
    return $this->setConstraint(new LimeConstraintUnlike($expected));
  }

  /**
   * Requires the parameter to contain the given value.
   *
   * @param  LimeConstraintInterface $constraint
   * @return LimeMockInvocationExpectation
   * @see    LimeConstraintContains
   */
  public function contains($expected)
  {
    return $this->setConstraint(new LimeConstraintContains($expected));
  }

  /**
   * Requires the parameter to not contain the given value.
   *
   * @param  LimeConstraintInterface $constraint
   * @return LimeMockInvocationExpectation
   * @see    LimeConstraintContainsNot
   */
  public function containsNot($expected)
  {
    return $this->setConstraint(new LimeConstraintContainsNot($expected));
  }

  /**
   * Requires the parameter to be greater than the given value.
   *
   * @param  LimeConstraintInterface $constraint
   * @return LimeMockInvocationExpectation
   * @see    LimeConstraintGreaterThan
   */
  public function greaterThan($expected)
  {
    return $this->setConstraint(new LimeConstraintGreaterThan($expected));
  }

  /**
   * Requires the parameter to be greater than or equal to the given value.
   *
   * @param  LimeConstraintInterface $constraint
   * @return LimeMockInvocationExpectation
   * @see    LimeConstraintGreaterThanEqual
   */
  public function greaterThanEqual($expected)
  {
    return $this->setConstraint(new LimeConstraintGreaterThanEqual($expected));
  }

  /**
   * Requires the parameter to be less than the given value.
   *
   * @param  LimeConstraintInterface $constraint
   * @return LimeMockInvocationExpectation
   * @see    LimeConstraintLessThan
   */
  public function lessThan($expected)
  {
    return $this->setConstraint(new LimeConstraintLessThan($expected));
  }

  /**
   * Requires the parameter to be less than or equal to the given value.
   *
   * @param  LimeConstraintInterface $constraint
   * @return LimeMockInvocationExpectation
   * @see    LimeConstraintLessThanEqual
   */
  public function lessThanEqual($expected)
  {
    return $this->setConstraint(new LimeConstraintLessThanEqual($expected));
  }
}