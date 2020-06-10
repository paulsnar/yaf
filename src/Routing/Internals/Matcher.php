<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;
use PN\Yaf\Http\Request;

class Matcher
{
  public $host;
  public $methods;
  public $path;
  public $pathArguments = [ ];

  public function __construct(array $constraints)
  {
    if ( ! array_key_exists('methods', $constraints) ||
         ! array_key_exists('path', $constraints)) {
      throw new \InvalidArgumentException('Cannot construct Matcher without ' .
        '`methods` and `path` constraints');
    }

    $this->host = $constraints['host'] ?? null;

    if ($constraints['methods'] === Wildcard::instance()) {
      $this->methods = Wildcard::instance();
    } else {
      $this->methods = [ ];
      foreach ($constraints['methods'] as $method) {
        $this->methods[$method] = true;
      }
    }

    if ($constraints['path'] === Wildcard::instance()) {
      $this->path = Wildcard::instance();
    } else {
      $this->path = explode('/', $constraints['path']);
      array_shift($this->path);
    }
  }

  public function doesMatch(Request $rq): bool
  {
    if ($this->host !== null) {
      if ($rq->headers->get('Host', null) !== $this->host) {
        return false;
      }
    }

    if ($this->methods !== Wildcard::instance()) {
      $methodMatches = $this->methods[$rq->method] ?? false;
      if ( ! $methodMatches) {
        return false;
      }
    }

    if ($this->path !== Wildcard::instance()) {
      $expectedPath = $this->path;
      $receivedPath = explode('/', $rq->path);
      array_shift($receivedPath);

      while ($expectedPath !== [ ] || $receivedPath !== [ ]) {
        $expected = array_shift($expectedPath);
        $received = array_shift($receivedPath);

        if ($expected[0] === ':') {
          $name = substr($expected, 1);
          $this->pathArguments[$name] = $received;
        } else if ($expected !== $received) {
          return false;
        }
      }
      if ($expectedPath !== [ ] || $receivedPath !== [ ]) {
        return false;
      }
    }

    return true;
  }
}
