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

  public function assertEquals(LimeTesterInterface $expected)
  {
    if (!$expected instanceof LimeTesterArray || $this->getType() !== $expected->getType())
    {
      throw new LimeTesterException($this, $expected);
    }

    $remaining = $this->value;

    foreach ($expected->value as $key => $value)
    {
      if (!array_key_exists($key, $remaining))
      {
        throw new LimeTesterException($this, $expected->dumpExcerpt($key, $value));
      }

      try
      {
        $remaining[$key]->assertEquals($value);
      }
      catch (LimeTesterException $e)
      {
        throw new LimeTesterException($this->dumpExcerpt($key, $e->getActual()), $expected->dumpExcerpt($key, $e->getExpected()));
      }

      unset($remaining[$key]);
    }

    foreach ($remaining as $key => $value)
    {
      throw new LimeTesterException($this->dumpExcerpt($key, $value), $expected);
    }
  }

  public function assertNotEquals(LimeTesterInterface $expected)
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
        $this->value[$key]->assertNotEquals($value);
      }
      catch (LimeTesterException $e)
      {
        throw new LimeTesterException($this, $expected);
      }
    }
  }

  public function assertContains(LimeTesterInterface $expected)
  {
    foreach ($this->value as $key => $value)
    {
      try
      {
        $value->assertEquals($expected);
        return;
      }
      catch (LimeTesterException $e)
      {
      }
    }

    throw new LimeTesterException($this->dumpAll(), $expected);
  }

  public function assertNotContains(LimeTesterInterface $expected)
  {
    foreach ($this->value as $key => $value)
    {
      $equal = true;

      try
      {
        $value->assertEquals($expected);
      }
      catch (LimeTesterException $e)
      {
        $equal = false;
      }

      if ($equal)
      {
        throw new LimeTesterException($this->dumpAll(), $expected);
      }
    }
  }

  public function __toString()
  {
    return $this->dumpExcerpt();
  }

  protected function getType()
  {
    return 'array';
  }

  protected function dumpAll()
  {
    $result = $this->getType().' (';

    if (!empty($this->value))
    {
      $result .= "\n";

      foreach ($this->value as $k => $v)
      {
        $result .= sprintf("  %s => %s,\n", var_export($k, true), $this->indent($v));
      }
    }

    $result .= ')';

    return $result;
  }

  protected function dumpExcerpt($key = null, $value = null)
  {
    $result = $this->getType().' (';

    if (!empty($this->value))
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
    }

    $result .= ')';

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