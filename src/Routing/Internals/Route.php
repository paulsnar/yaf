<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;

class Route
{
  public $host = null;
  public $methods;
  public $path;
  public $handler;
  public $middleware = [ ];

  public function __construct(array $constraints, HandlerInterface $handler)
  {
    $this->methods = $constraints['methods'] ?? null;
    if ($this->methods === null) {
      throw new \LogicException("Cannot define a route without methods");
    }

    $this->path = $constraints['path'] ?? null;
    if ($this->path === null) {
      throw new \LogicException("Cannot define a route without a path");
    }

    $this->host = $constraints['host'] ?? null;

    $this->handler = $handler;
  }

  public function id(): string
  {
    return implode('/', $this->methods) . ' ' . $this->path;
  }
}
