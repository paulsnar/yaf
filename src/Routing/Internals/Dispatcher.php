<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;
use FastRoute\{Dispatcher as FRDispatcher, RouteCollector};
use PN\Yaf\Http\{ErrorResponse, Request};
use function FastRoute\simpleDispatcher;

class Dispatcher
{
  private $hosts = [ ];
  private $rootDispatcher;
  private $routes = [ ];

  public function __construct(array $routes)
  {
    $hosts = [ ];
    $rootRoutes = [ ];

    foreach ($routes as $route) {
      $host = $route->constraints['host'] ?? null;
      if ($host === null) {
        $rootRoutes[] = $route;
        continue;
      }
      if ( ! array_key_exists($host, $hosts)) {
        $hosts[$host] = [ ];
      }
      $hosts[$host][] = $route;
    }

    $this->rootDispatcher = $this->makeDispatcher($rootRoutes);
    foreach ($hosts as $host => $routes) {
      $this->hosts[$host] = $this->makeDispatcher($routes);
    }
  }

  private function makeDispatcher(array $routes): array
  {
    $routeHash = [ ];
    $mapper = function (RouteCollector $r) use ($routes, &$routeHash) {
      foreach ($routes as $route) {
        $id = $route->id();
        $routeHash[$id] = $route;
        $r->addRoute($route->methods, $route->path, $id);
      }
    };
    $dispatcher = simpleDispatcher($mapper);
    return [$routeHash, $dispatcher];
  }

  public function dispatch(Request $rq): HandlerInterface
  {
    $host = $rq->host;
    if ($host === null || ! array_key_exists($host, $this->hosts)) {
      [$routes, $dispatcher] = $this->rootDispatcher;
    } else {
      [$routes, $dispatcher] = $this->hosts[$host];
    }

    $routeInfo = $dispatcher->dispatch($rq->method, $rq->path);
    switch ($routeInfo[0]) {
    case FRDispatcher::NOT_FOUND:
      return new ConstantHandler(ErrorResponse::notFound());

    case FRDispatcher::METHOD_NOT_ALLOWED:
      $methods = $routeInfo[1];
      $response = ErrorResponse::methodNotAllowed();
      $response->headers['Allow'] = implode(', ', $methods);
      return new ConstantHandler($response);

    case FRDispatcher::FOUND:
      $route = $routes[$routeInfo[1]];
      $rq->arguments = $routeInfo[2];
      return $route->handler;
    }
  }
}
