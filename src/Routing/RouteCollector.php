<?php declare(strict_types=1);
namespace PN\Yaf\Routing;
use PN\Yaf\Core\DependencyContainer;

class RouteCollector
{
  private $configurationStack = [ ];
  private $topConfiguration;

  private $routes = [ ];

  public function __construct()
  {
    $this->topConfiguration = new Internals\CollectorConfiguration();
  }

  public function setControllerAliases(array $aliases): void
  {
    foreach ($aliases as $alias => $class) {
      Handlers\ControllerHandler::registerAlias($alias, $class);
    }
  }

  public function group(array $config, \Closure $processor): void
  {
    $this->configurationStack[] = $this->topConfiguration;
    $this->topConfiguration = $this->topConfiguration->derive($config);

    $processor($this);

    $this->topConfiguration = array_pop($this->configurationStack);
  }

  public function route(array $constraints, $handler): void
  {
    $constraints = $this->topConfiguration->mergeConstraints($constraints);
    $matcher = new Internals\Matcher($constraints);

    $handler = new Handlers\ControllerHandler($handler);
    $handler = $this->topConfiguration->wrapHandler($handler);

    $route = new Internals\Route($matcher, $handler);
    $this->topConfiguration->processRoute($route);
    $this->routes[] = $route;
  }

  public function get(string $path, $handler): void
  {
    $this->route(['methods' => ['GET'], 'path' => $path], $handler);
  }

  public function post(string $path, $handler): void
  {
    $this->route(['methods' => ['POST'], 'path' => $path], $handler);
  }

  public function patch(string $path, $handler): void
  {
    $this->route(['methods' => ['PATCH'], 'path' => $path], $handler);
  }

  public function put(string $path, $handler): void
  {
    $this->route(['methods' => ['PUT'], 'path' => $path], $handler);
  }

  public function delete(string $path, $handler): void
  {
    $this->route(['methods' => ['DELETE'], 'path' => $path], $handler);
  }

  public function options(string $path, $handler): void
  {
    $this->route(['methods' => ['OPTIONS'], 'path' => $path], $handler);
  }

  public function wildcard(): Internals\Wildcard
  {
    return Internals\Wildcard::instance();
  }

  public function getRoutes(): array
  {
    return $this->routes;
  }
}
