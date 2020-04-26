<?php declare(strict_types=1);
namespace PN\Yaf\Tests\Functions;
use PHPUnit\Framework\TestCase;
use function PN\Yaf\array_omit;

class ArrayOmitTest extends TestCase
{
  public function testEmptyPassthrough(): void
  {
    self::assertEquals([], array_omit([]));
  }

  public function testPassthrough(): void
  {
    $map = ['a' => 1, 'b' => 2, 'c' => 3];
    self::assertEquals($map, array_omit($map));
  }

  public function testOmitsKeys(): void
  {
    $map = ['a' => 1, 'b' => 2, 'c' => 3];
    self::assertEquals(['a' => 1, 'b' => 2], array_omit($map, 'c'));
    self::assertEquals(['c' => 3], array_omit($map, 'b', 'a'));
  }

  public function testIgnoresNonexistentKeys(): void
  {
    $map = ['a' => 1, 'b' => 2, 'c' => 3];
    self::assertEquals(['b' => 2], array_omit($map, 'Z', 'a', 'c', 'd'));
    self::assertEquals($map, array_omit($map, 'Z', 'd'));
  }
}
