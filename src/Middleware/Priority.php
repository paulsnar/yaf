<?php declare(strict_types=1);
namespace PN\Yaf\Middleware;

abstract class Priority
{
  public const
    BEFORE_EARLY = -750,
    BEFORE_LATE = -250,
    AFTER_EARLY = 250,
    AFTER_LATE = 750;
}
