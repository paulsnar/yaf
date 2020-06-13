<?php declare(strict_types=1);
namespace PN\Yaf\Http;
use function PN\Yaf\json_decode;

class Request
{
  public
    $host,
    $method,
    $path,
    $headers,
    $body,
    $query,
    $form,
    $files,
    $cookies,
    $arguments = [ ],
    $properties = [ ];

  protected const IDEMPOTENT_METHODS = [
    'GET' => true,
    'HEAD' => true,
    'OPTIONS' => true,
  ];

  public static function fromGlobals(): self
  {
    $rq = new self();

    $rq->method = $_SERVER['REQUEST_METHOD'];
    $rq->headers = HeaderBag::fromGlobals();
    $rq->host = $rq->headers->get('Host');

    $path = $_SERVER['REQUEST_URI'];
    if (($queryStart = strpos($path, '?')) !== false) {
      $queryStr = substr($path, $queryStart + 1);
      parse_str($queryStr, $query);
      $path = substr($path, 0, $queryStart);
    } else {
      $query = $_GET;
    }
    $rq->path = $path;
    $rq->query = $query;

    if ( ! (self::IDEMPOTENT_METHODS[$rq->method] ?? false)) {
      $rq->body = file_get_contents('php://input');
      $rq->tryParseBody();
    }

    if ($rq->form === null && $_POST !== [ ]) {
      $rq->form = $_POST;
    }
    $rq->files = $_FILES;
    $rq->cookies = $_COOKIE;

    $rq->properties['network.remote_addr'] = $_SERVER['REMOTE_ADDR'];

    return $rq;
  }

  protected function tryParseBody(): void
  {
    $contentType = $this->headers['Content-Type'];
    if ($contentType === null) {
      return;
    }

    $contentType = new ParametrizedHeader($contentType);
    $type = $contentType->selfValue();
    $encoding = $contentType->parameter('encoding', 'UTF-8');

    if ($type !== 'application/x-www-form-urlencoded' &&
        $type !== 'application/json') {
      // TODO: support multipart form encoding
      return;
    }

    $body = $this->body;
    if (strtoupper($encoding) !== 'UTF-8') {
      $body = iconv($encoding, 'UTF-8', $body);
    }

    switch ($type) {
    case 'application/x-www-form-urlencoded':
      parse_str($body, $this->form);
      break;

    case 'application/json':
      $this->form = json_decode($body);
      break;
    }
  }
}
