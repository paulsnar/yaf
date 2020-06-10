<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Handlers;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Http\{Request, Response, Status};
use PN\Yaf\Routing\HandlerInterface;

class NotFoundHandler implements HandlerInterface
{
  public function handle(DependencyContainer $dc, Request $rq): Response
  {
    return new Response(Status::NOT_FOUND, [
      'Content-Type' => 'text/html; charset=UTF-8',
    ], "<!DOCTYPE html>\n<article>Sorry, not found.</article>\n");
  }
}
