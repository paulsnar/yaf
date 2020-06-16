<?php declare(strict_types=1);
namespace PN\Yaf\Tests\Routing;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Http\{Request, Response};
use PN\Yaf\Middleware\{Interrupt, MiddlewareInterface, Priority};
use PN\Yaf\Routing\Internals\{HandlerInterface, MiddlewareHandler};
use PHPUnit\Framework\{Assert, TestCase};

class MiddlewareHandlerTest extends TestCase
{
  public function testRemembersRequestAndResponse(): void
  {
    $request = new Request();
    $response = new Response();

    $handler = new class($request, $response) implements HandlerInterface {
      private $request, $response;
      public function __construct($request, $response) {
        $this->request = $request;
        $this->response = $response;
      }
      public function run(DependencyContainer $dc, Request $rq): Response {
        Assert::assertSame($this->request, $rq);
        return $this->response;
      }
    };
    $mw1 = new class($request) implements MiddlewareInterface {
      public static function getPriority(): int {
        return Priority::BEFORE_EARLY;
      }
      private $request;
      public function __construct($request) {
        $this->request = $request;
      }
      public function run(
          array $config, Request $rq, ?Response $resp): ?Response {
        Assert::assertSame($this->request, $rq);
        return null;
      }
    };
    $mw2 = new class($request, $response) implements MiddlewareInterface {
      public static function getPriority(): int {
        return Priority::AFTER_LATE;
      }
      private $request, $response;
      public function __construct($request, $response) {
        $this->request = $request;
        $this->response = $response;
      }
      public function run(
          array $config, Request $rq, ?Response $resp): ?Response {
        Assert::assertSame($this->request, $rq);
        Assert::assertSame($this->response, $resp);
        return $resp;
      }
    };

    $handler = new MiddlewareHandler($handler, [$mw1, $mw2]);
    self::assertSame($response,
      $handler->run(new DependencyContainer(), $request));
  }

  public function testPassesConfigToMiddleware(): void
  {
    $config = ['sentinel' => new class { }];
    $request = new Request();
    $request->properties['test.config'] = $config;

    $handler = new class implements HandlerInterface {
      public function run(DependencyContainer $dc, Request $rq): Response {
        return new Response();
      }
    };
    $middleware = new class implements MiddlewareInterface {
      public static function getPriority(): int {
        return Priority::BEFORE_EARLY;
      }

      public function run(
          array $config, Request $rq, ?Response $resp): ?Response {
        Assert::assertEquals($rq->properties['test.config'], $config);
        return $resp;
      }
    };

    $config = array_merge([$middleware], $config);
    $handler = new MiddlewareHandler($handler, [$config]);
    $handler->run(new DependencyContainer(), $request);
  }

  public function testRunsMiddlewareInPriorityOrder(): void
  {
    $request = new Request();
    $response = new Response();

    $latch = function (int $level) {
      static $prevLevel = 0;
      try {
        return $prevLevel;
      } finally {
        $prevLevel = $level;
      }
    };
    $request->properties['test.latch'] = $latch;

    $mw1 = new class implements MiddlewareInterface {
      public static function getPriority(): int {
        return Priority::BEFORE_EARLY;
      }
      public function run(
          array $config, Request $rq, ?Response $resp): ?Response {
        Assert::assertEquals(0, $rq->properties['test.latch'](1));
        Assert::assertNull($resp);
        return new Response(998);
      }
    };
    $mw2 = new class implements MiddlewareInterface {
      public static function getPriority(): int {
        return Priority::BEFORE_LATE;
      }
      public function run(
          array $config, Request $rq, ?Response $resp): ?Response {
        Assert::assertEquals(1, $rq->properties['test.latch'](2));
        Assert::assertEquals(998, $resp->status);
        return null;
      }
    };
    $mw3 = new class implements MiddlewareInterface {
      public static function getPriority(): int {
        return Priority::AFTER_EARLY;
      }
      public function run(
          array $config, Request $rq, ?Response $resp): ?Response {
        Assert::assertEquals(3, $rq->properties['test.latch'](4));
        Assert::assertEquals(999, $resp->status);
        $resp->properties['middleware.after-ran'] = true;
        return $resp;
      }
    };
    $mw4 = new class implements MiddlewareInterface {
      public static function getPriority(): int {
        return Priority::AFTER_LATE;
      }
      public function run(
          array $config, Request $rq, ?Response $resp): ?Response {
        Assert::assertEquals(4, $rq->properties['test.latch'](5));
        Assert::assertTrue($resp->properties['middleware.after-ran']);
        return $resp;
      }
    };

    $handler = new class implements HandlerInterface {
      public function run(DependencyContainer $dc, Request $rq): Response {
        Assert::assertEquals(2, $rq->properties['test.latch'](3));
        return new Response(999);
      }
    };

    $middleware = [$mw4, $mw3, $mw2, $mw1];
    $handler = new MiddlewareHandler($handler, $middleware);

    $handler->run(new DependencyContainer(), $request);
    self::assertEquals(5, $latch(-1));
  }

  public function testHandlesEarlyReturn(): void
  {
    $response = new Response();
    $middleware = new class($response) implements MiddlewareInterface {
      public static function getPriority(): int {
        return Priority::BEFORE_EARLY;
      }
      private $response;
      public function __construct($response) {
        $this->response = $response;
      }
      public function run(
          array $config, Request $rq, ?Response $resp): ?Response {
        return $this->response;
      }
    };
    $handler = new class implements HandlerInterface {
      public function run(DependencyContainer $dc, Request $rq): Response {
        Assert::fail('Middleware fell through');
      }
    };
    $handler = new MiddlewareHandler($handler, [$middleware]);
    self::assertSame($response,
      $handler->run(new DependencyContainer(), new Request()));
  }

  public function testHandlerInterrupt(): void
  {
    $response = new Response();
    $mw1 = new class($response) implements MiddlewareInterface {
      public static function getPriority(): int {
        return Priority::BEFORE_EARLY;
      }
      private $response;
      public function __construct($response) {
        $this->response = $response;
      }
      public function run(
          array $config, Request $rq, ?Response $resp): ?Response {
        throw new Interrupt($this->response);
      }
    };
    $mw2 = new class implements MiddlewareInterface {
      public static function getPriority(): int {
        return Priority::BEFORE_LATE;
      }
      public function run(
          array $config, Request $rq, ?Response $resp): ?Response {
        Assert::fail('Middleware interrupt fell through');
      }
    };
    $handler = new class implements HandlerInterface {
      public function run(DependencyContainer $dc, Request $rq): Response {
        Assert::fail('Middleware fell through');
      }
    };
    $handler = new MiddlewareHandler($handler, [$mw1, $mw2]);
    self::assertSame($response,
      $handler->run(new DependencyContainer(), new Request()));
  }
}
