<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Http\{Request, Response};

interface HandlerInterface
{
  public function run(DependencyContainer $dc, Request $rq): Response;
}
