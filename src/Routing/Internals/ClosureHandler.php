<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Http\{Request, Response};

class ClosureHandler implements HandlerInterface
{
  private $closure;

  public function __construct(\Closure $closure)
  {
    $this->closure = $closure;
  }

  public function run(DependencyContainer $dc, Request $rq): Response
  {
    return ($this->closure)($rq);
  }
}
