<?php declare(strict_types=1);
namespace PN\Yaf\Log;

abstract class LogLevel
{
  public const
    VERBOSE = 1,
    DEBUG = 2,
    INFO = 3,
    WARNING = 4,
    ERROR = 5;

  public const LEVEL_NAMES = [
    self::VERBOSE => 'VERBOSE',
    self::DEBUG => 'DEBUG',
    self::INFO => 'INFO',
    self::WARNING => 'WARNING',
    self::ERROR => 'ERROR',
  ];
}
