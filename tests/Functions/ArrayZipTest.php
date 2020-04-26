<?php declare(strict_types=1);
namespace PN\Yaf\Tests\Functions;
use PHPUnit\Framework\TestCase;
use function PN\Yaf\array_zip;

class ArrayZipTest extends TestCase
{
  public function testZipsNoArrays(): void
  {
    self::assertEquals([ ], array_zip());
  }

  public function testZipsEqualLengthArrays(): void
  {
    $a = [1, 2, 3];
    $b = ['a', 'b', 'c'];
    $zip = [[1, 'a'], [2, 'b'], [3, 'c']];
    self::assertEquals($zip, array_zip($a, $b));
  }

  public function testPreservesOrderWhenZipping(): void
  {
    $a = [1, 2, 3];
    $b = ['a', 'b', 'c'];
    $zip1 = [[1, 'a'], [2, 'b'], [3, 'c']];
    $zip2 = [['a', 1], ['b', 2], ['c', 3]];
    self::assertEquals($zip1, array_zip($a, $b));
    self::assertEquals($zip2, array_zip($b, $a));
  }

  public function testZipsUpToShortestArray(): void
  {
    $a = [1, 2, 3];
    $b = ['a', 'b'];
    $c = ['A', 'B', 'C', 'D'];
    $zip = [[1, 'a', 'A'], [2, 'b', 'B']];
    self::assertEquals($zip, array_zip($a, $b, $c));
  }
}
