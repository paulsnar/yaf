<?php declare(strict_types=1);
namespace PN\Yaf\Routing\Internals;
use PN\Yaf\Routing\HandlerInterface;

class Route
{
  public $matcher;
  public $handler;

  public function __construct(Matcher $matcher, HandlerInterface $handler)
  {
    $this->matcher = $matcher;
    $this->handler = $handler;
  }
}
