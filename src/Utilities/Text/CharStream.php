<?php declare(strict_types=1);
namespace PN\Yaf\Utilities\Text;

class CharStream
{
  private string $input;
  private int $position, $limit;
  public function __construct(string $input)
  {
    $this->input = $input;
    $this->position = 0;
    $this->limit = strlen($input);
  }

  public function isAtEnd(): bool
  {
    return $this->position >= $this->limit;
  }

  public function move(int $offset): void
  {
    $this->position += $offset;
  }

  public function peek(int $offset): ?string
  {
    $pos = $this->position + $offset;
    if ($pos < 0 || $pos >= $this->limit) {
      return null;
    }
    return $this->input[$pos];
  }

  public function consume(string $expected): string
  {
    if ($expected === '') {
      return '';
    }

    $len = strlen($expected);
    $start = $this->position;
    $end = $start + $len;

    if ($start < 0 || $start >= $this->limit ||
        $end < 0 || $end >= $this->limit) {
      throw new \RuntimeException("Cannot consume: out of bounds");
    }

    $part = substr($this->input, $start, $len);
    if ($part !== $expected) {
      throw new \RuntimeException("Consumption failed: " .
        "expected '{$expected}', got '{$part}'");
    }

    $this->position = $end;
    return $part;
  }

  public function consumeUntil(string $expected): string
  {
    $pos = strpos($this->input, $expected, $this->position);
    if ($pos === false) {
      throw new \RuntimeException(
        "Consumption failed: '{$expected}' not found in input");
    }

    $fragment = substr($this->input, $this->position,  $pos - $this->position);
    $this->position += strlen($fragment);
    return $fragment;
  }

  public function take(int $amount = 1): ?string
  {
    $start = $this->position;
    $end = $start + $amount;
    if ($start < 0 || $start >= $this->limit || $end < 0) {
      return null;
    }
    if ($end > $this->limit) {
      $have = $this->limit - $start;
      throw new \RuntimeException('Could not take: not enough input '.
        "(requested {$amount}, have {$have} bytes)");
    }

    $part = substr($this->input, $start, $amount);
    $this->position = $end;
    return $part;
  }
}
