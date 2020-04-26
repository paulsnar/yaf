<?php declare(strict_types=1);
namespace PN\Yaf\Routing;

interface RouteSet
{
  public function routes(RouteCollector $r): void;
  public function globalMiddleware(): array;
}
