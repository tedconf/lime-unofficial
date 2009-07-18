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
 * Control class for lime mock objects
 *
 * Each mock object has a control class assigned that controls the mock's
 * workflow. The control class knows which method calls the mock expects
 * and how to react on them. The control class is also used to switch between
 * the recording and the playing state (by calling replay()) and to verify
 * whether all expected method calls have been made (by calling verify()).
 *
 * Usually you will not want to call the methods in this class directly. You
 * should rather use the same methods in the mock object directly or the
 * static methods in LimeMock, in case you don't want to generate the methods
 * in your mock. The latter can be useful if you want to mock one of the method
 * names in this class (for instance "replay()"). For more information, see
 * the documentation of LimeMock.
 *
 * @package    sfLimeExtraPlugin
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
class LimeMockControl implements LimeMockInterface
{

  /**
   * The controlled mock object
   * @var LimeMockInterface
   */
  private $mock = null;

  /**
   * The LimeTest instance used to report failing and passing tests
   * @var LimeTest
   */
  private $test = null;

  /**
   * Whether the control is in replay mode
   * @var bool
   */
  private $replay = false;

  /**
   * The last called method name and parameters
   * @var array
   */
  private $currentMethod = array();

  /**
   * A string representation of the last called method name and parameters.
   * @var string
   */
  private $currentMethodHash = '';

  /**
   * The configured return values for each method/parameter combination
   * @var array
   */
  private $returnValues = array();

  /**
   * The configured exceptions for each method/parameter combination
   * @var array
   */
  private $exceptions = array();

  /**
   * The expectation collection used to track the method calls
   * @var lime_expectation_collection
   */
  private $expectationList = null;

  /**
   * Constructor.
   *
   * @param LimeMockInterface $mock  The controlled mock object
   * @param LimeTest $test            The test instance to report upon verification
   */
  public function __construct(LimeTest $test = null)
  {
    $this->test = $test;
    $this->expectationList = new LimeExpectationBag($test);
  }

  /**
   * Callback method for a method call in the mock object.
   *
   * @param  string $method      The called method name
   * @param  array  $parameters  The parameters
   * @return mixed               In recording mode, the mock object is returned.
   *                             In replay mode, the configured return value
   *                             is returned.
   */
  public function call($method, array $parameters)
  {
    $this->currentMethod = array($method, $parameters);
    $this->currentMethodHash = md5(serialize($this->currentMethod));

    if ($this->replay)
    {
      $this->expectationList->addActual($this->currentMethod);

      if (array_key_exists($this->currentMethodHash, $this->exceptions))
      {
        throw new $this->exceptions[$this->currentMethodHash]();
      }

      if (array_key_exists($this->currentMethodHash, $this->returnValues))
      {
        return $this->returnValues[$this->currentMethodHash];
      }
    }
    else
    {
      $this->expectationList->addExpected($this->currentMethod);

      return $this;
    }
  }

  /**
   * Configures the mock to expect exactly no method call.
   */
  public function setExpectNothing()
  {
    $this->expectationList->setExpectNothing();
  }

  /**
   * (non-PHPdoc)
   * @see lib/lime_verifiable#setFailOnVerify()
   */
  public function setFailOnVerify()
  {
    $this->expectationList->setFailOnVerify();
  }

  /**
   * (non-PHPdoc)
   * @see lib/lime_verifiable#setStrict()
   */
  public function setStrict()
  {
    $this->expectationList->setStrict();
  }

  /**
   * (non-PHPdoc)
   * @see lib/mock/LimeMockInterface#replay()
   */
  public function replay()
  {
    $this->replay = true;
  }

  /**
   * (non-PHPdoc)
   * @see lib/lime_verifiable#verify()
   */
  public function verify()
  {
    $this->expectationList->verify();
  }

  /**
   * (non-PHPdoc)
   * @see lib/mock/LimeMockInterface#returns($value)
   */
  public function returns($value)
  {
    $this->returnValues[$this->currentMethodHash] = $value;

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see lib/mock/LimeMockInterface#times($count)
   */
  public function times($count)
  {
    for ($i = 0; $i < $count - 1; ++$i)
    {
      $this->expectationList->addExpected($this->currentMethod);
    }

    return $this;
  }

  /**
   * (non-PHPdoc)
   * @see lib/mock/LimeMockInterface#throws($exception)
   */
  public function throws($exception)
  {
    $this->exceptions[$this->currentMethodHash] = $exception;

    return $this;
  }

}