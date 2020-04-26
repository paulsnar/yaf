<?php declare(strict_types=1);
namespace PN\Yaf\Http\Events;
use PN\Yaf\Events\Event;
use PN\Yaf\Http\{Request, Response};

class ResponseSentEvent extends Event
{
  public const NAME = 'http.response.sent';
  public function getName(): string
  {
    return self::NAME;
  }

  protected $request, $response;

  public function __construct(Response $response, ?Request $request = null)
  {
    $this->request = $request;
    $this->response = $response;
  }

  public function getRequest(): ?Request
  {
    return $this->request;
  }

  public function getResponse(): Response
  {
    return $this->response;
  }
}
