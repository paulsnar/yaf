<?php declare(strict_types=1);
namespace PN\Yaf\Core;

abstract class Controller
{
  public function __construct(string $alias)
  {
    Internals\ControllerRegistry::register($alias, static::class);
  }
}
