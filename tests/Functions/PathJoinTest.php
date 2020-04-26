<?php declare(strict_types=1);
namespace PN\Yaf\Tests\Functions;
use PHPUnit\Framework\TestCase;
use function PN\Yaf\path_join;

class PathJoinTest extends TestCase
{
  public function testJoinsNothingToEmptyString(): void
  {
    self::assertEquals('', path_join());
  }

  public function testPassesThroughSingleArgument(): void
  {
    self::assertEquals('somePath', path_join('somePath'));
  }

  public function testImplodesWithDirectorySeparator(): void
  {
    self::assertEquals(
      'tests' . DIRECTORY_SEPARATOR . 'Functions' . DIRECTORY_SEPARATOR .
        'PathJoinTest.php',
      path_join('tests', 'Functions', 'PathJoinTest.php'));
  }

  public function testPassesThroughEmbeddedSlashes(): void
  {
    self::assertEquals(
      'abc/def' . DIRECTORY_SEPARATOR . 'ghi\\jkl',
      path_join('abc/def', 'ghi\\jkl'));
  }
}
