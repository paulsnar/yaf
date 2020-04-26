<?php declare(strict_types=1);
namespace PN\Yaf\Events;

abstract class Event
{
  abstract public function getName(): string;

  protected $isPropagationStopped = false;

  public function isPropagationStopped(): bool
  {
    return $this->isPropagationStopped;
  }

  public function stopPropagation(): void
  {
    $this->isPropagationStopped = true;
  }
}
