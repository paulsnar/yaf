<?php declare(strict_types=1);
namespace PN\Yaf\Events;

class EventDispatcher
{
  protected $handlers = [ ];

  public function addEventListener(
    string $event,
    \Closure $callback,
    int $priority = 0
  ): Subscription {
    $handler = new AnonymousEventHandler($callback, $priority);
    if (array_key_exists($event, $this->handlers)) {
      $this->handlers[$event][] = $handler;
    } else {
      $this->handlers[$event] = [$handler];
    }

    return new Subscription($this, $event, $handler);
  }

  public function addEventHandler(
    string $event,
    EventHandler $handler
  ): Subscription {
    if (array_key_exists($event, $this->handlers)) {
      $this->handlers[$event][] = $handler;
    } else {
      $this->handlers[$event] = [$handler];
    }

    return new Subscription($this, $event, $handler);
  }

  public function addEventSubscriber(EventSubscriber $subscriber): void
  {
    $subscriptions = [ ];
    foreach ($subscriber->getSubscriptions() as $name => $handler) {
      $subscriptions[$name] = $this->addEventHandler($name, $handler);
    }
    if ($subscriber instanceof SubscriptionAware) {
      $subscriber->setEventSubscriptions($subscriptions);
    }
  }

  public function removeEventHandler(string $event, EventHandler $handler): void
  {
    if ( ! array_key_exists($event, $this->handlers)) {
      return;
    }

    $handlers =& $this->handlers[$event];

    $index = array_search($handler, $handlers, true);
    if ($index === false) {
      return;
    }

    array_splice($handlers, $index, 1);
  }

  public function dispatchEvent(Event $event): void
  {
    $handlers = $this->handlers[$event->getName()] ?? null;
    if ($handlers === null) {
      return;
    }

    usort($handlers, function (EventHandler $a, EventHandler $b) {
      return $a->getPriority() <=> $b->getPriority();
    });

    foreach ($handlers as $handler) {
      $handler->handleEvent($event);
      if ($event->isPropagationStopped()) {
        break;
      }
    }
  }
}
