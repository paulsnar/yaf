<?php declare(strict_types=1);
namespace PN\Yaf\Log;
use PN\Yaf\Core\{Configuration, Environment};
use function PN\Yaf\{logfmt_encode, str_slice};
use function PN\Yaf\Debug\format_backtrace;

abstract class Logger
{
  protected array $context = [ ];
  private int $filter = LogLevel::INFO;

  public function __construct(Configuration $config)
  {
    if ($config->mustGet('environment') === Environment::DEV) {
      $this->filter = LogLevel::VERBOSE;
    }
    $this->filter = $config->get('log.filter', $this->filter);
  }

  public function mergeContext(array $context): void
  {
    $this->context = $context + $this->context;
  }

  abstract protected function writeLine(string $line): void;

  public function log(int $level, string $event, array $context): void
  {
    if ($level < $this->filter) {
      return;
    }

    $context = $context + $this->context;
    $context['level'] = LogLevel::LEVEL_NAMES[$level] ?? ((string) $level);

    $ts = date('Y-m-d H:i:s O');
    if (str_slice($ts, -4) === '0000') {
      $ts = str_slice($ts, 0, -6) . 'Z';
    }

    $context['timestamp'] = $ts;
    $context['event'] = $event;

    $line = logfmt_encode($context);
    $this->writeLine($line . "\n");
  }

  public function trace(int $level, string $event, array $context = [ ]): void
  {
    $trace = debug_backtrace();
    array_shift($trace); // remove self
    $context['trace'] = format_backtrace($trace);
    $this->log($level, $event, $context);
  }

  public function exception(
    int $level,
    string $event,
    \Throwable $exception,
    array $context = [ ]
  ): void {
    if ($previous = $exception->getPrevious()) {
      $this->logException($level, $event, $previous,
        ['has_next' => 'true'] + $context);
      $context['has_previous'] = 'true';
    }

    $context['exception'] = get_class($exception);
    $context['message'] = $exception->getMessage();
    $context['file'] = $exception->getFile() ?: '<unknown>';
    $context['line'] = (string) ($exception->getLine() ?: 0);
    $context['trace'] = format_backtrace($exception->getTrace());
    $this->log($level, $event, $context);
  }

  public function verbose(string $event, array $context = [ ]): void
  {
    $this->log(LogLevel::VERBOSE, $event, $context);
  }

  public function debug(string $event, array $context = [ ]): void
  {
    $this->log(LogLevel::DEBUG, $event, $context);
  }

  public function info(string $event, array $context = [ ]): void
  {
    $this->log(LogLevel::INFO, $event, $context);
  }

  public function warning(string $event, array $context = [ ]): void
  {
    $this->log(LogLevel::WARNING, $event, $context);
  }

  public function error(string $event, array $context = [ ]): void
  {
    $this->log(LogLevel::ERROR, $event, $context);
  }
}
