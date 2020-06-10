<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;
use PN\Yaf\Routing\HandlerInterface;
use PN\Yaf\Routing\Handlers\MiddlewareHandler;

class CollectorConfiguration
{
  private $constraints = [ ];
  private $middleware = [ ];
  private $prefix = '';

  public function derive(array $config): self
  {
    $derivative = clone $this;
    if (array_key_exists('prefix', $config)) {
      $derivative->prefix .= $config['prefix'];
    }
    if (array_key_exists('host', $config)) {
      $derivative->constraints['host'] = $config['host'];
    }
    if (array_key_exists('middleware', $config)) {
      $derivative->middleware = array_merge(
        $this->middleware, $config['middleware']);
    }
    return $derivative;
  }

  public function mergeConstraints(array $constraints): array
  {
    foreach ($this->constraints as $name => $value) {
      if ( ! array_key_exists($name, $constraints)) {
        $constraints[$name] = $value;
      }
    }
    if ($this->prefix !== '') {
      $constraints['path'] = $this->prefix . $constraints['path'];
    }
    return $constraints;
  }

  public function wrapHandler(HandlerInterface $handler): HandlerInterface
  {
    if ($this->middleware !== [ ]) {
      $handler = new MiddlewareHandler($handler, $this->middleware);
    }
    return $handler;
  }

  public function processRoute(Route $route): void
  {
  }
}
