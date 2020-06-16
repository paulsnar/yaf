<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Http\{Request, Response};
use PN\Yaf\Middleware\{Interrupt, MiddlewareInterface};

class MiddlewareHandler implements HandlerInterface
{
  private $delegate, $middleware;

  public function __construct(HandlerInterface $delegate, array $middleware)
  {
    $this->delegate = $delegate;
    $this->middleware = $middleware;
  }

  public function run(DependencyContainer $dc, Request $request): Response
  {
    $middleware = $this->middleware;
    $middleware = array_map(function ($middleware) {
      if (is_array($middleware)) {
        $config = $middleware;
        $class = array_shift($config);
      } else {
        $class = $middleware;
        $config = [ ];
      }

      $isValid = is_string($class) || is_object($class);
      if (is_object($class)) {
        $isValid = $isValid && ($class instanceof MiddlewareInterface);
      }
      if ( ! $isValid) {
        throw new \InvalidArgumentException("Bad middleware specification");
      }

      return [$class, $config];
    }, $middleware);
    usort($middleware, function ($a, $b) {
      return $a[0]::getPriority() <=> $b[0]::getPriority();
    });

    $before = null;
    $after = null;
    for ($i = 0; $i < count($middleware); $i += 1) {
      $class = $middleware[$i][0];
      if ($class::getPriority() >= 0) {
        $before = array_slice($middleware, 0, $i);
        $after = array_slice($middleware, $i);
        break;
      }
    }
    if ($before === null) {
      $before = $middleware;
    }

    $response = null;

    $run = function (array $middleware) use ($dc, $request, &$response): bool {
      foreach ($middleware as [$class, $config]) {
        if (is_string($class)) {
          $class = $dc->get($class);
        }

        try {
          $response = $class->run($config, $request, $response);
        } catch (Interrupt $exc) {
          $response = $exc->response;
          return true;
        }
      }
      return false;
    };

    if ($before !== null) {
      if ($run($before) || $response !== null) {
        return $response;
      }
    }
    $response = $this->delegate->run($dc, $request);
    if ($after !== null) {
      $run($after);
    }
    return $response;
  }
}
