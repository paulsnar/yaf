<?php declare(strict_types=1);
namespace PN\Yaf\Tests\Functions;
use PHPUnit\Framework\TestCase;
use function PN\Yaf\array_pluck;

class ArrayPluckTest extends TestCase
{
  public function testEmptyPassthrough(): void
  {
    self::assertEquals([], array_pluck([]));
  }

  public function testReturnsEmptyArrayWhenNoKeys(): void
  {
    $map = ['a' => 1, 'b' => 2, 'c' => 3];
    self::assertEquals([], array_pluck($map));
  }

  public function testPlucksChosenKeys(): void
  {
    $map = ['a' => 1, 'b' => 2, 'c' => 3];
    self::assertEquals(['a' => 1], array_pluck($map, 'a'));
    self::assertEquals(['a' => 1, 'b' => 2], array_pluck($map, 'a', 'b'));
  }

  public function testIgnoresAbsentKeys(): void
  {
    $map = ['a' => 1, 'b' => 2, 'c' => 3];
    self::assertEquals($map, array_pluck($map, 'Z', 'a', 'b', 'c', 'd'));
    self::assertEquals([], array_pluck($map, 'Z', 'd'));
  }
}
