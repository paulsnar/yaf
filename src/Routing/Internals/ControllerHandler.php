<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Http\{Request, Response};

class ControllerHandler implements HandlerInterface
{
  private $class, $controller, $method;

  public function __construct($classOrObject, string $method)
  {
    if (is_string($classOrObject)) {
      $this->class = $classOrObject;
    } else {
      $this->controller = $classOrObject;
    }

    $this->method = $method;
  }

  public function run(DependencyContainer $dc, Request $rq): Response
  {
    if ($this->controller !== null) {
      $controller = $this->controller;
    } else {
      $controller = $dc->get($this->class);
    }
    return $controller->{$this->method}($rq);
  }
}
