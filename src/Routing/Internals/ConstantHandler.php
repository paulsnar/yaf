<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;
use PN\Yaf\Core\DependencyContainer;
use PN\Yaf\Http\{Request, Response};

class ConstantHandler implements HandlerInterface
{
  private $response;

  public function __construct(Response $response)
  {
    $this->response = $response;
  }

  public function run(DependencyContainer $dc, Request $rq): Response
  {
    return $this->response;
  }
}
