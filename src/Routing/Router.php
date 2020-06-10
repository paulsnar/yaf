<?php declare(strict_types=1);
namespace PN\Yaf\Routing;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Http\{Request, Response};

class Router
{
  private $dispatcher;

  public function __construct(RouteSet $routeSet)
  {
    $routeCollector = new RouteCollector();
    $routeSet->draw($routeCollector);
    $this->dispatcher = new Dispatcher($routeCollector->getRoutes());
  }

  public function dispatch(DependencyContainer $dc, Request $rq): Response
  {
    $handler = $this->dispatcher->getHandlerFor($rq);
    return $handler->handle($dc, $rq);
  }
}
