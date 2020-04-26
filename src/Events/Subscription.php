<?php declare(strict_types=1);
namespace PN\Yaf\Events;

class Subscription
{
  private $dispatcher, $name, $handler;
  protected $isValid = true;

  public function __construct(
    EventDispatcher $dispatcher,
    string $name,
    EventHandler $handler
  ) {
    $this->dispatcher = $dispatcher;
    $this->name = $name;
    $this->handler = $handler;
  }

  public function unsubscribe(): void
  {
    if ( ! $this->isValid) {
      return;
    }

    $this->dispatcher->removeEventHandler($this->name, $this->handler);
    $this->dispatcher = $this->handler = null;
    $this->isValid = false;
  }
}
