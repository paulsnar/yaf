<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Events;
use PN\Yaf\Events\Event;
use PN\Yaf\Http\{Request, Response};

class WillDispatchEvent extends Event
{
  public const NAME = 'routing.will_dispatch';
  public function getName(): string
  {
    return self::NAME;
  }

  protected $request, $response;

  public function __construct(Request $request)
  {
    $this->request = $request;
    $this->response = null;
  }

  public function getRequest(): Request
  {
    return $this->request;
  }

  public function setRequest(Request $request): void
  {
    $this->request = $request;
  }

  public function getResponse(): ?Response
  {
    return $this->response;
  }

  public function setResponse(Response $response): void
  {
    $this->stopPropagation();
    $this->response = $response;
  }
}
