<?php declare(strict_types=1);
namespace PN\Yaf\Routing;
use PN\Yaf\Http\{Request, Response};
use PN\Yaf\Routing\Internal\Route;

class RouteCollector
{
  private $config, $parent, $routes;

  public function __construct(array $config, ?RouteCollector $parent = null)
  {
    $this->config = $config;
    if ($parent !== null) {
      $this->parent = $parent;
    } else {
      $this->routes = [ ];
    }
  }

  public function getRoutes(): ?array
  {
    return $this->routes;
  }

  private function mergeConfig(array $config): array
  {
    if (array_key_exists('middleware', $this->config)) {
      $config['middleware'] = array_merge(
        $this->config['middleware'], $config['middleware'] ?? [ ]);
    }
    if (array_key_exists('prefix', $this->config)) {
      $config['prefix'] = $this->config['prefix'] . ($config['prefix'] ?? '');
    }
    $config = $config + $this->config;

    return $config;
  }

  public function route(
    array $verbs,
    string $path,
    $controller,
    ?string $method = null,
    array $config = [ ]
  ): void {
    $config = $this->mergeConfig($config);

    if (array_key_exists('prefix', $config)) {
      $path = $config['prefix'] . $path;
      unset($config['prefix']);
    }

    if ($this->parent !== null) {
      $this->parent->route($verbs, $path, $controller, $method, $config);
    } else {
      $this->routes[] = new Route($verbs, $path, $controller, $method, $config);
    }
  }

  public function get(
    string $path,
    $controller,
    ?string $method = null,
    array $config = [ ]
  ): void {
    $this->route(['GET'], $path, $controller, $method, $config);
  }

  public function post(
    string $path,
    $controller,
    ?string $method = null,
    array $config = [ ]
  ): void {
    $this->route(['POST'], $path, $controller, $method, $config);
  }

  public function put(
    string $path,
    $controller,
    ?string $method = null,
    array $config = [ ]
  ): void {
    $this->route(['PUT'], $path, $controller, $method, $config);
  }

  public function delete(
    string $path,
    $controller,
    ?string $method = null,
    array $config = [ ]
  ): void {
    $this->route(['DELETE'], $path, $controller, $method, $config);
  }

  public function patch(
    string $path,
    $controller,
    ?string $method = null,
    array $config = [ ]
  ): void {
    $this->route(['PATCH'], $path, $controller, $method, $config);
  }

  public function redirect(string $from, string $to): void
  {
    $handler = function (Request $rq) use ($to): Response {
      return Response::redirectTo($to);
    };
    $this->get($from, $handler);
  }

  public function group(array $config, \Closure $callback): void
  {
    $collector = new RouteCollector($config, $this);
    $callback($collector);
  }
}
