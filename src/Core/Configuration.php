<?php declare(strict_types=1);
namespace PN\Yaf\Core;
use function PN\Yaf\path_join;

class Configuration
{
  protected $root;
  protected $values;

  public function __construct(?string $root)
  {
    $this->root = $root;

    if ($root !== null) {
      $this->load();
    }
  }

  public static function fromArray(array $values): self
  {
    $conf = new Configuration(null);
    $conf->values = $values;
    return $conf;
  }

  protected function load(): void
  {
    $path = path_join($this->root, 'config.php');
    if ( ! is_file($path)) {
      throw new \RuntimeException("Config file {$path} not found");
    }

    $this->values = require $path;
  }

  public function get(string $key, $default = null)
  {
    $path = explode('.', $key);
    $node = $this->values;
    foreach ($path as $fragment) {
      if ( ! array_key_exists($fragment, $node)) {
        return $default;
      }
      $node = $node[$fragment];
    }
    return $node;
  }

  public function mustGet(string $key)
  {
    $sentinel = new \stdClass();
    $value = $this->get($key, $sentinel);
    if ($value === $sentinel) {
      throw new \RuntimeException("Required config key not set: {$key}");
    }
    return $value;
  }

  public function set(string $key, $value): void
  {
    $path = explode('.', $key);
    $leaf = array_pop($path);
    $node =& $this->values;
    foreach ($path as $fragment) {
      if ( ! array_key_exists($fragment, $node)) {
        $node[$fragment] = [ ];
      }
      $node =& $node[$fragment];
    }
    $node[$leaf] = $value;
  }

  public function getEnvironment(): int
  {
    return $this->get('environment', Environment::PRODUCTION);
  }

  public function getRoot(): string
  {
    return $this->root;
  }

  public function isDebug(): bool
  {
    return $this->getEnvironment() === Environment::DEV;
  }
}
