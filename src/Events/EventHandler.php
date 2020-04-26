<?php declare(strict_types=1);
namespace PN\Yaf\Events;

interface EventHandler
{
  public function getPriority(): int;
  // public function handleEvent(Event $event): void;
}
