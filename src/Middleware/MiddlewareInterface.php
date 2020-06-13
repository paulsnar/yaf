<?php declare(strict_types=1);
namespace PN\Yaf\Middleware;
use PN\Yaf\Http\{Request, Response};

interface MiddlewareInterface
{
  public function run(Request $rq, ?Response $resp): ?Response;
}
