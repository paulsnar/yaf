<?php declare(strict_types=1);
namespace PN\Yaf\Http;

class ParametrizedHeader
{
  private $selfValue, $parameters = [ ];

  public function __construct(string $header)
  {
    $parts = explode(';', $header);
    $this->selfValue = array_shift($parts);
    foreach ($parts as $keyValue) {
      if ($keyValue[0] === ' ') {
        $keyValue = substr($keyValue, 1);
      }
      $keyValue = explode('=', $keyValue, 2);
      if (count($keyValue) === 1) {
        $keyValue[] = true;
        continue;
      }
      [$key, $value] = $keyValue;
      $this->parameters[$key] = $value;
    }
  }

  public function selfValue(): string
  {
    return $this->selfValue;
  }

  public function parameter(string $key, ?string $default = null): ?string
  {
    return $this->parameters[$key] ?? $default;
  }
}
