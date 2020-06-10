<?php declare(strict_types=1);
namespace PN\Yaf\Routing;
use PN\Yaf\Http\Request;

class Dispatcher
{
  private $routes = [ ];

  public function __construct(array $routes)
  {
    $this->routes = $routes;
  }

  public function getHandlerFor(Request $rq): HandlerInterface
  {
    foreach ($this->routes as $route) {
      if ($route->matcher->doesMatch($rq)) {
        $rq->arguments = $route->matcher->pathArguments;
        return $route->handler;
      }
    }
    return new Handlers\NotFoundHandler();
  }
}
