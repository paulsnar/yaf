<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;
use function PN\Yaf\str_starts_with;

class CollectorConfiguration
{
  protected $controllerAliases = [ ];
  protected $middleware = [ ];
  protected $prefix = '';
  protected $host = null;

  public function __construct()
  {
  }

  public function derive(array $config): self
  {
    $derivative = clone $this;

    if (array_key_exists('prefix', $config)) {
      $derivative->prefix .= $config['prefix'];
    }
    if (array_key_exists('host', $config)) {
      $derivative->host = $config['host'];
    }
    if (array_key_exists('middleware', $config)) {
      $derivative->middleware = array_merge(
        $this->middleware, $config['middleware']);
    }

    return $derivative;
  }

  public function mergeControllerAliases(array $aliases): void
  {
    foreach ($aliases as $name => $controller) {
      $this->controllerAliases[$name] = $controller;
    }
  }

  public function mergeConstraints(array $constraints): array
  {
    if ($this->prefix !== '') {
      $constraints['path'] = $this->prefix . $constraints['path'];
    }

    if ($this->host !== null) {
      $constraints['host'] = $this->host;
    }

    return $constraints;
  }

  public function mergeMiddleware(array $middleware): array
  {
    return array_merge($middleware, $this->middleware);
  }

  public function makeHandler($handler, array $middleware): HandlerInterface
  {
    if (is_object($handler)) {
      $handler = new ClosureHandler($handler);
    } else {
      if (is_string($handler)) {
        $handler = explode('#', $handler);
      }
      [$controller, $method] = $handler;
      if (is_string($controller) &&
          array_key_exists($controller, $this->controllerAliases)) {
        $controller = $this->controllerAliases[$controller];
      }
      $handler = new ControllerHandler($controller, $method);
    }

    if ($middleware !== [ ]) {
      $handler = new MiddlewareHandler($handler, $middleware);
    }

    return $handler;
  }

  public function makeRoute(array $constraints, $handler, array $config): Route
  {
    $constraints = $this->mergeConstraints($constraints);
    $middleware = $this->mergeMiddleware($config['middleware'] ?? [ ]);

    $handler = $this->makeHandler($handler, $middleware);

    return new Route($constraints, $handler);
  }
}
