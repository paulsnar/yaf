<?php declare(strict_types=1);
namespace PN\Yaf\Debug;
use PN\Yaf\Http\{Response, Status};

class HttpExceptionResponse extends Response
{
  protected static function formatException(\Throwable $exc): string
  {
    $body = sprintf("%s: %s\n at %s:%d\n",
      get_class($exc), $exc->getMessage(),
      $exc->getFile(), $exc->getLine());
    $body .= format_backtrace($exc->getTrace());
    return $body;
  }

  public function __construct(\Throwable $exc)
  {
    $body = "--- Sorry, an exception has occured. ---\n";
    $body .= static::formatException($exc);
    while ($exc = $exc->getPrevious()) {
      $body .= "\n\n" . static::formatException($exc);
    }

    parent::__construct(Status::INTERNAL_SERVER_ERROR, [
      'Content-Type' => 'text/plain; charset=UTF-8',
    ], $body);
  }
}
