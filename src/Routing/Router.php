<?php declare(strict_types=1);
namespace PN\Yaf\Routing;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Events\EventDispatcher;
use PN\Yaf\Http\{ErrorResponse, Request, Response, Status};
use PN\Yaf\Http\Events\{RequestEvent, RequestDispatchEvent, ResponseEvent};
use PN\Yaf\Routing\Events\{WillDispatchEvent, DidDispatchEvent};
use PN\Yaf\Routing\Internal\Route;

class Router
{
  protected $dispatcher, $eventDispatcher, $globalMiddleware;

  public function __construct(EventDispatcher $eventDispatcher, RouteSet $conf)
  {
    $this->eventDispatcher = $eventDispatcher;

    $this->globalMiddleware = $conf->globalMiddleware();
    $mapper = function ($r) use ($conf) {
      $collector = new RouteCollector([ ]);
      $conf->routes($collector);
      foreach ($collector->getRoutes() as $route) {
        $r->addRoute($route->verbs, $route->path, $route);
      }
    };
    $this->dispatcher = \FastRoute\simpleDispatcher($mapper);
  }

  public function dispatch(DependencyContainer $dc, Request $request): array
  {
    $event = new WillDispatchEvent($request);
    $this->eventDispatcher->dispatchEvent($event);
    $response = $event->getResponse();
    if ($response !== null) {
      return $response;
    }

    $status = $this->dispatcher->dispatch($request->method, $request->path);
    $useGlobalMiddleware = true;

    $route = null;
    $response = null;

    switch ($status[0]) {
    case \FastRoute\Dispatcher::NOT_FOUND:
      $response = ErrorResponse::notFound();
      break;

    case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
      $response = ErrorResponse::methodNotAllowed();
      break;

    case \FastRoute\Dispatcher::FOUND:
      [$status, $route, $request->arguments] = $status;
      $useGlobalMiddleware = $route->useGlobalMiddleware;
      break;

    default:
      throw new RoutingException(
        "Unknown FastRoute return code: {$status[0]}");
    }

    if ($useGlobalMiddleware) {
      foreach ($this->globalMiddleware as $middleware) {
        if (is_string($middleware)) {
          $middleware = $dc->get($middleware);
        }
        $this->eventDispatcher->addEventSubscriber($middleware);
      }
    }

    if ($route !== null) {
      foreach ($route->middleware as $middleware) {
        if (is_string($middleware)) {
          $middleware = $dc->get($middleware);
        }
        $this->eventDispatcher->addEventSubscriber($middleware);
      }
    }

    $event = new RequestEvent($request);
    $this->eventDispatcher->dispatchEvent($event);
    $request = $event->getRequest();

    $event = new RequestDispatchEvent($request);
    $this->eventDispatcher->dispatchEvent($event);
    $request = $event->getRequest();
    $response = $event->getResponse();

    if ($response === null) {
      if ($route === null) {
        throw new RoutingException('Lost route for request ' .
          $request->method . ' ' . $request->path);
      }

      $response = $route->invoke($dc, $request);
    }

    $event = new DidDispatchEvent($request, $response);
    $this->eventDispatcher->dispatchEvent($event);
    $response = $event->getResponse();

    $event = new ResponseEvent($response, $request);
    $this->eventDispatcher->dispatchEvent($event);
    $response = $event->getResponse();

    return [$request, $response];
  }
}
