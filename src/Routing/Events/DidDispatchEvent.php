<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Events;
use PN\Yaf\Events\Event;
use PN\Yaf\Http\{Request, Response};

class DidDispatchEvent extends Event
{
  public const NAME = 'routing.did_dispatch';
  public function getName(): string
  {
    return self::NAME;
  }

  protected $request, $response;

  public function __construct(Request $request, Response $response)
  {
    $this->request = $request;
    $this->response = $response;
  }

  public function getRequest(): Request
  {
    return $this->request;
  }

  public function getResponse(): Response
  {
    return $this->response;
  }

  public function setResponse(Response $response): void
  {
    $this->response = $response;
  }
}
