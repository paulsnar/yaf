<?php declare(strict_types=1);
namespace PN\Yaf\Tests\Functions;
use PHPUnit\Framework\TestCase;
use function PN\Yaf\str_starts_with;

class StrStartsWithTest extends TestCase
{
  public function testChecksForStringPrefix(): void
  {
    self::assertTrue(str_starts_with('hello world', 'hello'));
    self::assertFalse(str_starts_with('hello world', 'world'));
    self::assertFalse(str_starts_with('hello world', 'test'));
  }

  public function testDoesNotFailOnEmptyHaystackOrNeedle(): void
  {
    // TODO: perhaps this should warn?
    self::assertFalse(str_starts_with('hello', ''));
    self::assertFalse(str_starts_with('', 'hi'));
  }

  public function testMatchesWholeStringAsPrefix(): void
  {
    self::assertTrue(str_starts_with('hello', 'hello'));
  }
}
