<?php declare(strict_types=1);
namespace PN\Yaf\Http;

class HeaderBag implements \ArrayAccess, \Iterator
{
  protected $bag = [ ];
  protected $iterator;

  public function __construct(array $headers)
  {
    foreach ($headers as $name => $value) {
      $this->bag[strtolower($name)] = $value;
    }
  }

  public static function fromGlobals(): self
  {
    $headers = [ ];
    foreach ($_SERVER as $key => $value) {
      if (strpos($key, 'HTTP_') === 0) {
        $key = substr($key, 5);
        $key = str_replace('_', '-', $key);
        $headers[$key] = $value;
      }
    }
    return new static($headers);
  }

  public function has(string $name): bool
  {
    return array_key_exists(strtolower($name), $this->bag);
  }

  public function get(string $key, ?string $default = null): ?string
  {
    return $this->bag[strtolower($key)] ?? $default;
  }

  public function set(string $key, string $value): void
  {
    $this->bag[strtolower($key)] = $value;
  }

  public function delete(string $key): void
  {
    unset($this->bag[strtolower($key)]);
  }

  public function toArray(): array
  {
    return $this->bag;
  }

  public function offsetExists($name) { return $this->has($name); }
  public function offsetGet($name) { return $this->get($name); }
  public function offsetSet($name, $value) { $this->set($name, $value); }
  public function offsetUnset($name) { $this->delete($name); }

  public function rewind()
  {
    $iterate = function () {
      $bag = $this->bag;
      foreach ($bag as $key => $value) {
        yield $key => $value;
      }
    };
    $this->iterator = $iterate();
    return $this->iterator->rewind();
  }
  public function key() { return $this->iterator->key(); }
  public function current() { return $this->iterator->current(); }
  public function next() { return $this->iterator->next(); }
  public function valid()
  {
    if ($this->iterator === null) {
      return false;
    }
    return $this->iterator->valid();
  }
}
