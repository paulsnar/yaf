<?php declare(strict_types=1);
namespace PN\Yaf\Middleware;
use PN\Yaf\Http\{Request, Response};

interface MiddlewareInterface
{
  public static function getPriority(): int;
  public function run(array $config, Request $rq, ?Response $resp): ?Response;
}
