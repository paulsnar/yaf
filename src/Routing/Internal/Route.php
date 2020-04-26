<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internal;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Http\{Request, Response};

class Route
{
  public
    $verbs,
    $path,
    $handler,
    $useGlobalMiddleware,
    $middleware;

  public function __construct(
    array $verbs,
    string $path,
    $controller,
    ?string $method = null,
    array $config
  ) {
    $this->verbs = $verbs;
    $this->path = $path;

    $this->useGlobalMiddleware = $config['use_global_middleware'] ?? true;
    $this->middleware = $config['middleware'] ?? [ ];

    if ($method !== null) {
      $this->handler = [$controller, $method];
    } else if (is_callable($controller)) {
      $this->handler = $controller;
    } else {
      throw new \InvalidArgumentException('Invalid $controller argument ' .
        'to RouteCollector::route -- must be callable or a controller class ' .
        'or instance');
    }
  }

  public function invoke(DependencyContainer $dc, Request $rq): Response
  {
    $handler = $this->handler;
    if (is_array($handler)) {
      [$controller, $method] = $handler;
      if (is_string($controller)) {
        $controller = $dc->get($controller);
      }
      $handler = [$controller, $method];
    }
    return $handler($rq);
  }
}
