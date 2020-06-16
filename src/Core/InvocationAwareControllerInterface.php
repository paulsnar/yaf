<?php declare(strict_types=1);
namespace PN\Yaf\Core;
use PN\Yaf\Http\{Request, Response};

interface InvocationAwareControllerInterface
{
  public function yafInvoke(string $method, Request $request): Response;
}
