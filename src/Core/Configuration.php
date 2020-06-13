<?php declare(strict_types=1);
namespace PN\Yaf\Core;
use function PN\Yaf\path_join;

class Configuration
{
  private $dc;
  protected $root;
  protected $values = [ ];

  public function __construct(DependencyContainer $dc, string $root)
  {
    $this->dc = $dc;
    $this->root = $root;

    $this->load();
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

  public function isDebug(): bool
  {
    return $this->getEnvironment() === Environment::DEV;
  }
}
