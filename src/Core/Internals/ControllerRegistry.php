<?php declare(strict_types=1);
namespace PN\Yaf\Core\Internals;

abstract class ControllerRegistry
{
  private static $registry = [ ];

  public static function register(string $name, string $class): void
  {
    self::$registry[$name] = $class;
  }

  public static function resolve(string $name): ?string
  {
    return self::$registry[$name] ?? null;
  }
}
