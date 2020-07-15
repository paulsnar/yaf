<?php declare(strict_types=1);
namespace PN\Yaf\Log;
use PN\Yaf\Core\Configuration;

class StdoutLogger extends Logger
{
  private $stream;

  public function __construct(Configuration $config)
  {
    parent::__construct($config);
    $this->stream = fopen('php://stdout', 'w');
  }

  protected function writeLine(string $line): void
  {
    fwrite($this->stream, $line);
  }
}
