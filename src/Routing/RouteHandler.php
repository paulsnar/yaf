<?php declare(strict_types=1);
namespace PN\Yaf\Routing;

class RouteHandler
{
  public static function from($thing): self
  {
    if (is_object($thing)) {
      if ($thing implements HandlerInterface) {
        return new self($thing);
      }
    } else if (is_string($thing)) {
      $parts = explode('#', $thing);
      if (count($parts) !== 2) {
        throw new \InvalidArgumentException(
          'Malformed handler string: ' . $thing);
      }

      [$controller, $method] = $parts;
      return new Handlers\ControllerHandler($controller, $method);
    }
  }
}
