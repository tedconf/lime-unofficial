<?php

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class LimeTesterArray extends LimeTester
{
  protected
    $type = 'array';

  public function __construct(array $array)
  {
    foreach ($array as $key => $value)
    {
      $array[$key] = LimeTester::create($value);
    }

    parent::__construct($array);
  }

  public function assertEquals($expected, $strict = false)
  {
    if (!$expected instanceof LimeTesterArray || $this->getType() !== $expected->getType())
    {
      throw new LimeNotEqualException($this, $expected);
    }

    $remaining = $this->value;

    foreach ($expected->value as $key => $value)
    {
      if (!array_key_exists($key, $remaining))
      {
        throw new LimeNotEqualException($this, $expected->excerpt($key, $value));
      }

      try
      {
        $remaining[$key]->assertEquals($value, $strict);
      }
      catch (LimeNotEqualException $e)
      {
        throw new LimeNotEqualException($this->excerpt($key, $e->getActual()), $expected->excerpt($key, $e->getExpected()));
      }

      unset($remaining[$key]);
    }

    foreach ($remaining as $key => $value)
    {
      throw new LimeNotEqualException($this->excerpt($key, $value), $expected);
    }
  }

  public function assertNotEquals($expected, $strict = false)
  {
    if (!$expected instanceof LimeTesterArray || $this->getType() !== $expected->getType())
    {
      return;
    }

    foreach ($expected->value as $key => $value)
    {
      if (!array_key_exists($key, $this->value))
      {
        return;
      }

      try
      {
        $this->value[$key]->assertNotEquals($value, $strict);
      }
      catch (LimeNotEqualException $e)
      {
        throw new LimeNotEqualException($this->excerpt(), $expected->excerpt());
      }
    }
  }

  public function __toString()
  {
    return $this->excerpt();
  }

  protected function getType()
  {
    return 'array';
  }

  protected function excerpt($key = null, $value = null)
  {
    $result = $this->getType().' (';

    if (empty($this->value))
    {
      $result .= ')';
    }
    else
    {
      $truncated = false;
      $result .= "\n";

      foreach ($this->value as $k => $v)
      {
        if ((is_null($key) || $key != $k) && !$truncated)
        {
          $result .= "  ...\n";
          $truncated = true;
        }
        else if ($k == $key)
        {
          $value = is_null($value) ? $v : $value;
          $result .= sprintf("  %s => %s,\n", var_export($k, true), $this->indent($value));
          $truncated = false;
        }
      }

      $result .= ')';
    }

    return $result;
  }

  protected function indent($lines)
  {
    $lines = explode("\n", $lines);

    foreach ($lines as $key => $line)
    {
      $lines[$key] = '  '.$line;
    }

    return trim(implode("\n", $lines));
  }
}