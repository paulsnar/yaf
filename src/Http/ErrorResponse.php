<?php declare(strict_types=1);
namespace PN\Yaf\Http;

abstract class ErrorResponse
{
  protected const ERROR_BODY_TEMPLATE = <<<'HTML'
<!DOCTYPE html>
<article>{message}</article>

HTML;

  protected const
    MESSAGE_DEFAULT = 'Sorry, something went wrong.',
    MESSAGE_FORBIDDEN = "Sorry, but you're not allowed to do that.",
    MESSAGE_NOT_FOUND = 'Sorry, not found.';
  protected const ERROR_BODY_CONTENT_TYPE = 'text/html; charset=UTF-8';

  public static function errorBody(string $message): string
  {
    $message = htmlspecialchars($message, ENT_HTML5);
    return str_replace('{message}', $message, self::ERROR_BODY_TEMPLATE);
  }

  public static function genericResponse(
    ?string $message = null,
    int $status = Status::INTERNAL_SERVER_ERROR
  ): Response {
    if ($message === null) {
      $message = self::MESSAGE_DEFAULT;
    }
    return new Response($status, [
      'Content-Type' => self::ERROR_BODY_CONTENT_TYPE,
    ], static::errorBody($message));
  }

  public static function internalServerError(): Response
  {
    return static::genericResponse();
  }

  public static function forbidden(): Response
  {
    return static::genericResponse(self::MESSAGE_FORBIDDEN, Status::FORBIDDEN);
  }

  public static function notFound(): Response
  {
    return static::genericResponse(self::MESSAGE_NOT_FOUND, Status::NOT_FOUND);
  }

  public static function methodNotAllowed(): Response
  {
    return static::genericResponse(null, Status::METHOD_NOT_ALLOWED);
  }
}

