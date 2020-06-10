<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;

final class Wildcard
{
  public static function instance(): self
  {
    static $instance;
    if ($instance === null) {
      $instance = new self();
    }
    return $instance;
  }
}
