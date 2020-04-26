<?php declare(strict_types=1);
namespace PN\Yaf\Http\Events;
use PN\Yaf\Events\Event;
use PN\Yaf\Http\Request;

class RequestEvent extends Event
{
  public const NAME = 'http.request';
  public function getName(): string
  {
    return self::NAME;
  }

  protected $request;

  public function __construct(Request $request)
  {
    $this->request = $request;
  }

  public function getRequest(): Request
  {
    return $this->request;
  }

  public function setRequest(Request $request): void
  {
    $this->request = $request;
  }
}
