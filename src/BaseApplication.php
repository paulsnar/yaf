<?php declare(strict_types=1);
namespace PN\Yaf;
use PN\Yaf\Core\{Configuration, DependencyContainer};
use PN\Yaf\Debug\HttpExceptionResponse;
use PN\Yaf\Http\{ErrorResponse, Request, Response};
use PN\Yaf\Runtime\ShutdownJobRunner;
use PN\Yaf\Routing\Router;

abstract class BaseApplication
{
  public $dc;
  protected $root;
  protected $config = null;

  public function __construct(string $root)
  {
    ob_start();
    set_error_handler(function ($severity, $message, $file, $line) {
      throw new \ErrorException($message, 0, $severity, $file, $line);
    });
    set_exception_handler(function (\Throwable $exc) {
      $response = $this->generateExceptionResponse($exc);
      $response->send();
      ob_end_flush();
    });

    $this->root = $root;

    $this->dc = new DependencyContainer();
    $this->dc->store($this);

    $this->config = new Configuration($root);
    $this->dc->store($this->config);
  }

  private function generateExceptionResponse(\Throwable $exc): Response
  {
    // $isDebug = false;
    // if ($this->config !== null) {
    //   $isDebug = $this->config->isDebug();
    // }
    $isDebug = true;

    if ($isDebug) {
      return new HttpExceptionResponse($exc);
    }
    return ErrorResponse::internalServerError();
  }

  protected function handleUncaughtException(\Throwable $exc): void
  {
    // override...
  }

  public function run()
  {
    try {
      $router = $this->dc->get(Router::class);
      $request = Request::fromGlobals();
      $response = $router->dispatch($this->dc, $request);
    } catch (\Throwable $exc) {
      $this->handleUncaughtException($exc);
      $response = $this->generateExceptionResponse($exc);
    } finally {
      $response->send();

      try {
        $this->dc->get(ShutdownJobRunner::class)->run();
      } catch (\Throwable $exc) {
        $this->handleUncaughtException($exc);
        // noop (or TODO log?)
      }
    }
  }
}
