<?php declare(strict_types=1);
namespace PN\Yaf\Routing;

interface RouteSet
{
  public function draw(RouteCollector $r): void;
}
