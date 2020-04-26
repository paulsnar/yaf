<?php declare(strict_types=1);
namespace PN\Yaf\Tests\Functions;
use PHPUnit\Framework\TestCase;
use function PN\Yaf\str_maskpos;

class StrMaskposTest extends TestCase
{
  public function testFindsEarliestMaskCharOccurence(): void
  {
    self::assertEquals(0, str_maskpos('hello world', 'dehlo'));
    self::assertEquals(1, str_maskpos('hello world', 'e'));
    self::assertEquals(2, str_maskpos('hello world', 'l'));
  }

  public function testReturnsNullOnUnmatchingMask(): void
  {
    self::assertNull(str_maskpos('hello world', 'a'));
    self::assertNull(str_maskpos('hello world', 'abc'));
  }

  public function testDoesNotFailOnEmptyMask(): void
  {
    // TODO: maybe should warn?
    self::assertNull(str_maskpos('hello', ''));
  }

  public function testSkipsNotFoundCharacters(): void
  {
    self::assertEquals(1, str_maskpos('hello', 'abcde'));
  }
}
