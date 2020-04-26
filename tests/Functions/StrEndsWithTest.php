<?php declare(strict_types=1);
namespace PN\Yaf\Tests\Functions;
use PHPUnit\Framework\TestCase;
use function PN\Yaf\str_ends_with;

class StrEndsWithTest extends TestCase
{
  public function testChecksForStringSuffix(): void
  {
    self::assertTrue(str_ends_with('hello world', 'world'));
    self::assertFalse(str_ends_with('hello world', 'hello'));
    self::assertFalse(str_ends_with('hello world', 'test'));
  }

  public function testDoesNotFailOnEmptyHaystackOrNeedle(): void
  {
    // TODO: perhaps this should warn?
    self::assertFalse(str_ends_with('hello', ''));
    self::assertFalse(str_ends_with('', 'hi'));
  }

  public function testMatchesWholeStringAsSuffix(): void
  {
    self::assertTrue(str_ends_with('hello', 'hello'));
  }
}
