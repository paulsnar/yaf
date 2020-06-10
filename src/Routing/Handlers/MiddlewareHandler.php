<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Handlers;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Http\{Request, Response};
use PN\Yaf\Middleware\Interrupt;
use PN\Yaf\Routing\HandlerInterface;

class MiddlewareHandler implements HandlerInterface
{
  private $before = [ ];
  private $after = [ ];
  private $handler;

  public function __construct(HandlerInterface $handler, array $middleware)
  {
    $this->handler = $handler;

    foreach ($middleware as $class) {
      $priority = $class::PRIORITY;
      if ($priority < 0) {
        $this->before[] = $class;
      } else {
        $this->after[] = $class;
      }
    }

    usort($this->before, function ($a, $b) {
      return $a::PRIORITY <=> $b::PRIORITY;
    });
    usort($this->after, function ($a, $b) {
      return $a::PRIORITY <=> $b::PRIORITY;
    });
  }

  public function handle(DependencyContainer $dc, Request $rq): Response
  {
    $response = null;

    foreach ($this->before as $middleware) {
      $middleware = $dc->get($middleware);
      try {
        $response = $middleware->run($rq, $response);
      } catch (Interrupt $exc) {
        return $exc->response;
      }
    }

    if ($response === null) {
      $response = $this->handler->handle($dc, $rq);
    }

    foreach ($this->after as $middleware) {
      $middleware = $dc->get($middleware);
      try {
        $response = $middleware->run($rq, $response);
      } catch (Interrupt $exc) {
        return $exc->response;
      }
    }

    return $response;
  }
}
