<?php declare(strict_types=1);
namespace PN\Yaf\Events;

class AnonymousEventHandler implements EventHandler
{
  private $callback, $priority;

  public function __construct(\Closure $callback, int $priority)
  {
    $this->callback = $callback;
    $this->priority = $priority;
  }

  public function getPriority(): int
  {
    return $this->priority;
  }

  public function handleEvent(Event $event): void
  {
    ($this->callback)($event);
  }
}
