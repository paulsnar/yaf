<?php declare(strict_types=1);
namespace PN\Yaf\Events;

interface SubscriptionAware
{
  public function setEventSubscriptions(array $subscriptions): void;
}
