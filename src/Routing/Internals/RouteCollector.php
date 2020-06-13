<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;

class RouteCollector
{
  private $configStack = [ ];
  private $config;

  private $routes = [ ];

  public function __construct()
  {
    $this->config = new CollectorConfiguration();
  }

  public function getRoutes(): array
  {
    return $this->routes;
  }

  public function alias(array $aliases): void
  {
    $this->config->mergeControllerAliases($aliases);
  }

  public function group(array $config, \Closure $processor): void
  {
    $this->configStack[] = $this->config;
    $this->config = $this->config->derive($config);

    $processor($this);

    $this->config = array_pop($this->configStack);
  }

  public function route(
    array $constraints,
    $handler,
    array $config = [ ]
  ): void {
    $this->routes[] = $this->config->makeRoute($constraints, $handler, $config);
  }

  private function addMethodRoute(
    string $method,
    string $path,
    $handler,
    array $config = [ ]
  ): void {
    $this->route([
      'methods' => [$method],
      'path' => $path,
    ], $handler, $config);
  }

  public function get(string $path, $handler, array $config = [ ]): void
  {
    $this->addMethodRoute('GET', $path, $handler, $config);
  }

  public function post(string $path, $handler, array $config = [ ]): void
  {
    $this->addMethodRoute('POST', $path, $handler, $config);
  }

  public function patch(string $path, $handler, array $config = [ ]): void
  {
    $this->addMethodRoute('PATCH', $path, $handler, $config);
  }

  public function put(string $path, $handler, array $config = [ ]): void
  {
    $this->addMethodRoute('PUT', $path, $handler, $config);
  }

  public function delete(string $path, $handler, array $config = [ ]): void
  {
    $this->addMethodRoute('DELETE', $path, $handler, $config);
  }

  public function options(string $path, $handler, array $config = [ ]): void
  {
    $this->addMethodRoute('OPTIONS', $path, $handler, $config);
  }
}
