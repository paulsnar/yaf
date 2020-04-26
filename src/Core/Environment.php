<?php declare(strict_types=1);
namespace PN\Yaf\Core;

abstract class Environment
{
  public const
    DEV = 1,
    PRODUCTION = 2;

  public const ENVIRONMENTS = [
    'dev' => self::DEV,
    'production' => self::PRODUCTION,
  ];
}
