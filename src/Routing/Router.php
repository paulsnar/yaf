<?php declare(strict_types=1);
namespace PN\Yaf\Routing;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Http\{Request, Response};

class Router
{
  private $dispatcher;

  public function __construct(RouteSet $routes)
  {
    $collector = new RouteCollector();
    $routes->draw($collector);
    $routes = $collector->getRoutes();
    $this->dispatcher = new Dispatcher($routes);
  }

  public function dispatch(DependencyContainer $dc, Request $rq): Response
  {
    $handler = $this->dispatcher->dispatch($rq);
    return $handler->run($dc, $rq);
  }
}
