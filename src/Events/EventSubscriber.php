<?php declare(strict_types=1);
namespace PN\Yaf\Events;

interface EventSubscriber
{
  public function getSubscriptions(): array;
}
