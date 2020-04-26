<?php declare(strict_types=1);
namespace PN\Yaf\Tests\Functions;
use PHPUnit\Framework\TestCase;
use function PN\Yaf\vector_map;

class VectorMapTest extends TestCase
{
  public function testPassthrough(): void
  {
    $array = [0, 1, 2, 3];
    $map = ['a' => 'A', 'b' => 'B', 'c' => 'C'];

    $passthroughValue = function ($value) { return $value; };
    $passthroughGen = function ($value, $key) { yield $key => $value; };

    self::assertEquals($array, vector_map($passthroughValue, $array));
    self::assertEquals($array, vector_map($passthroughGen, $array));
    self::assertEquals($map, vector_map($passthroughValue, $map));
    self::assertEquals($map, vector_map($passthroughGen, $map));
  }

  public function testKeyPreservation(): void
  {
    $subject = ['a' => 'A', 'b' => 'B', 'c' => 'C'];
    $mapper = function ($value) { return $value . $value; };
    $result = ['a' => 'AA', 'b' => 'BB', 'c' => 'CC'];
    self::assertEquals($result, vector_map($mapper, $subject));
  }

  public function testMapKeyRemapping(): void
  {
    $subject = ['a' => 'A', 'b' => 'B', 'c' => 'C'];
    $mapper = function ($value, $key) { yield $key . $key => $value; };
    $result = ['aa' => 'A', 'bb' => 'B', 'cc' => 'C'];
    self::assertEquals($result, vector_map($mapper, $subject));
  }

  public function testArrayKeyRemapping(): void
  {
    $subject = [1, 2, 3];
    $mapper = function ($value, $key) {
      yield $key * $key => $value * $value;
    };
    $result = [1, 4, 4 => 9];
    self::assertEquals($result, vector_map($mapper, $subject));
  }

  public function testEmptyMap(): void
  {
    $mapper = function () {
      throw new \RuntimeException('Called mapper with empty array');
    };
    self::assertEquals([ ], vector_map($mapper, [ ]));
  }
}
