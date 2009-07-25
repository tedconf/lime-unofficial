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
 * Abstract base type for all expectation collections.
 *
 * You can use expectation collections to test whether certain expectations
 * have been met. You can "feed" the collection with expectations by calling
 * addExpected(). By calling addActual(), you can inform the collection about
 * the actual values. By calling verify() you can check whether the actual
 * values matched the expected ones.
 *
 * By default, this class compares the expected and actual values type
 * insensitive. You can change this behaviour by calling setStrict().
 *
 * Usually, an exception is thrown as soon as you call addActual() with an
 * unexpected value. This helps to analyze the problem very quickly. If you
 * want to suppress exceptions and instead receive a failing test once
 * verify() is called, you can call setFailOnVerify().
 *
 * In your application you must use one of the concrete subclasses to make
 * use of this functionality. The differences between the subclasses are
 * how they take the order and amount of items into account.
 *
 * Classes that take the order into account expect the value to be added in
 * the exact same order as they were expected. Classes that respect the
 * amount require the value to be added the same number of times as it was
 * expected.
 *
 * The following table lists how the different subclasses treat these
 * properties:
 *
 * <table>
 *  <tr>
 *    <th>Class</th>
 *    <th>Amount</th>
 *    <th>Order</th>
 *  </tr>
 *  <tr>
 *    <td>LimeExpectationList</td>
 *    <td>Yes</td>
 *    <td>Yes</td>
 *  </tr>
 *  <tr>
 *    <td>LimeExpectationSet</td>
 *    <td>No</td>
 *    <td>No</td>
 *  </tr>
 *  <tr>
 *    <td>LimeExpectationBag</td>
 *    <td>Yes</td>
 *    <td>No</td>
 *  </tr>
 * </table>
 *
 * @package    lime
 * @author     Bernhard Schussek <bschussek@gmail.com>
 * @version    SVN: $Id$
 */
abstract class LimeExpectationCollection
{

  /**
   * A reference to a LimeTest instance
   * @var LimeTest
   */
  protected $output = null;

  /**
   * The array of actual values
   * @var array
   */
  protected $actual = array();

  /**
   * The array of expected values
   * @var array
   */
  protected $expected = array();

  /**
   * Whether the class should fail when it retrieves unexpected values
   * @var bool
   */
  protected $failOnVerify = false;

  protected $expectNothing = false;

  /**
   * Whether the comparison between expected and actual values should be
   * type-safe.
   * @var bool
   */
  protected $strict = false;

  public function __construct(LimeOutputInterface $output = null)
  {
    $this->output = $output;
  }

  /**
   * (non-PHPdoc)
   * @see lib/lime_verifiable#setFailOnVerify()
   */
  public function setFailOnVerify()
  {
    $this->failOnVerify = true;
  }

  /**
   * (non-PHPdoc)
   * @see lib/lime_verifiable#setStrict()
   */
  public function setStrict()
  {
    $this->strict = true;
  }

  /**
   * (non-PHPdoc)
   * @see lib/lime_verifiable#verify()
   */
  public function verify()
  {
    if (is_null($this->output))
    {
      throw new BadMethodCallException("A LimeTest object is required for verification");
    }

    list ($file, $line) = LimeTrace::findCaller('LimeExpectationCollection');

    if (count($this->expected) == 0)
    {
      $this->output->pass('No values have been expected', $file, $line);
    }
    else if ($this->strict ? ($this->actual === $this->expected) : ($this->actual == $this->expected))
    {
      $this->output->pass('The expected values have been set', $file, $line);
    }
    else
    {
      $this->output->fail('The expected values have been set', $file, $line);
    }
  }

  public function setExpectNothing()
  {
    $this->expectNothing = true;
  }

  /**
   * Adds an expected value to the collection.
   *
   * @param $value
   */
  public function addExpected($value)
  {
    $this->expected[] = $value;
  }

  /**
   * Adds an actual value to the collection.
   *
   * @param $value
   */
  public function addActual($value)
  {
    $ignoreValue = count($this->expected) == 0 && !$this->expectNothing;
    if (!$this->failOnVerify && !$ignoreValue && !$this->isExpected($value))
    {
      throw new LimeAssertionException('Unexpected value', $value);
    }

    $this->actual[] = $value;
  }

  abstract protected function isExpected($value);

}