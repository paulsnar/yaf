<?php declare(strict_types=1);
namespace PN\Yaf\Http;

class Session implements \ArrayAccess
{
  private static bool $isStarted = false;
  private const COOKIE_NAME = '_session';

  private static function start(): void
  {
    if (self::$isStarted) {
      return;
    }

    session_start([
      'name' => self::COOKIE_NAME,
      'cookie_httponly' => true,
      'cookie_samesite' => 'Strict',
      'cookie_secure' => true,
    ]);
    self::$isStarted = true;
  }

  public function has(string $name): bool
  {
    self::start();
    return array_key_exists($name, $_SESSION);
  }

  public function get(string $key, ?string $default = null)
  {
    self::start();
    return $_SESSION[$key] ?? $default;
  }

  public function set(string $key, $value): void
  {
    self::start();
    $_SESSION[$key] = $value;
  }

  public function delete(string $key): void
  {
    self::start();
    unset($_SESSION[$key]);
  }

  public function clear(): Cookie
  {
    self::start();
    session_destroy();
    session_unset();
    return Cookie::delete(self::COOKIE_NAME);
  }

  public function offsetExists($key) { return $this->has($key); }
  public function offsetGet($key) { return $this->get($key); }
  public function offsetSet($key, $value) { $this->set($key, $value); }
  public function offsetUnset($key) { $this->delete($key); }
}
