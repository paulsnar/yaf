<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Handlers;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Core\Internals\ControllerRegistry;
use PN\Yaf\Http\{Request, Response};
use PN\Yaf\Routing\HandlerInterface;

class ControllerHandler implements HandlerInterface
{
  protected static $controllers = [ ];

  public static function registerAlias(string $alias, string $class): void
  {
    self::$controllers[$alias] = $class;
  }

  private $controller, $method;

  public function __construct(string $descriptor)
  {
    $parts = explode('#', $descriptor);
    if (count($parts) !== 2) {
      throw new \InvalidArgumentException(
        'Invalid controller descriptor: ' . $descriptor);
    }
    [$controller, $this->method] = $parts;
    $resolvedController = self::$controllers[$controller] ?? null;
    if ($resolvedController === null) {
      throw new \InvalidArgumentException(
        'Unknown controller alias: ' . $controller);
    }
    $this->controller = $resolvedController;
  }

  public function handle(DependencyContainer $dc, Request $rq): Response
  {
    $controller = $dc->get($this->controller);
    $response = $controller->{$this->method}($rq);
    return $response;
  }
}
