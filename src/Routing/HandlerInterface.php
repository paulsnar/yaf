<?php declare(strict_types=1);
namespace PN\Yaf\Routing;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Http\{Request, Response};

interface HandlerInterface
{
  public function handle(DependencyContainer $dc, Request $rq): Response;
}
