<?php declare(strict_types=1);
namespace PN\Yaf\Middleware;
use PN\Yaf\Http\Response;

class Interrupt extends \RuntimeException
{
  public $response;

  public function __construct(Response $response)
  {
    $this->response = $response;
    parent::__construct();
  }
}
