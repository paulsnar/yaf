<?php declare(strict_types=1);
namespace PN\Yaf\Http;
use function PN\Yaf\json_encode;

class Response
{
  public
    $status,
    $headers,
    $cookies = [ ],
    $body,
    $properties = [ ];

  public function __construct(
    int $status = Status::NO_CONTENT,
    array $headers = [ ],
    $body = null
  ) {
    $this->status = $status;
    $this->headers = new HeaderBag($headers);
    $this->body = $body;
  }

  public static function withJson(
    $thing,
    int $status = Status::OK,
    array $headers = [ ]
  ): self {
    $thing = json_encode($thing);
    return new self($status, [
      'Content-Type' => 'application/json; charset=UTF-8',
    ] + $headers, $thing);
  }

  public static function redirectTo(string $targetPath): self
  {
    $url = htmlspecialchars($targetPath, ENT_QUOTES | ENT_HTML5);
    $msg = <<<HTML
<!DOCTYPE html>
<article>You are being <a href="{$url}">redirected.</a></article>

HTML;

    return new self(Status::FOUND, [
      'Content-Type' => 'text/html; charset=UTF-8',
      'Location' => $targetPath,
    ], $msg);
  }

  public function send(): void
  {
    http_response_code($this->status);

    foreach ($this->headers->toArray() as $key => $value) {
      header("{$key}: {$value}");
    }
    if ( ! $this->headers->has('Content-Type')) {
      // prevent the dumb PHP default
      header('Content-Type:');
      header_remove('Content-Type');
    }

    foreach ($this->cookies as $cookie) {
      $cookie->send();
    }

    if ($this->body !== null) {
      if (is_resource($this->body)) {
        fpassthru($this->body);
      } else {
        echo $this->body;
      }
    }

    if (function_exists('\\fastcgi_finish_request')) {
      \fastcgi_finish_request();
    }
  }
}
