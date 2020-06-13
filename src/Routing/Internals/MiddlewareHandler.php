<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Http\{Request, Response};
use PN\Yaf\Middleware\Interrupt;

class MiddlewareHandler implements HandlerInterface
{
  private $delegate, $middleware;

  public function __construct(HandlerInterface $delegate, array $middleware)
  {
    $this->delegate = $delegate;
    $this->middleware = $middleware;
  }

  public function run(DependencyContainer $dc, Request $request): Response
  {
    $before = [ ];
    $after = [ ];

    foreach ($this->middleware as $middleware) {
      if ($middleware::PRIORITY < 0) {
        $before[] = $middleware;
      } else {
        $after[] = $middleware;
      }
    }

    usort($before, function ($a, $b) {
      return $a::PRIORITY <=> $b::PRIORITY;
    });
    usort($after, function ($a, $b) {
      return $a::PRIORITY <=> $b::PRIORITY;
    });

    $response = null;
    foreach ($before as $middleware) {
      $middleware = $dc->get($middleware);
      try {
        $response = $middleware->run($request, $response);
      } catch (Interrupt $exc) {
        return $exc->response;
      }
    }

    if ($response === null) {
      $this->delegate->run($dc, $request);
    }

    foreach ($after as $middleware) {
      $middleware = $dc->get($middleware);
      try {
        $response = $middleware->run($request, $response);
      } catch (Interrupt $exc) {
        return $exc->response;
      }
    }

    return $response;
  }
}
