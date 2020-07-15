<?php declare(strict_types=1);
namespace PN\Yaf\Utilities\Text;

class LogfmtDecoder
{
  private CharStream $input;

  public function __construct(string $line)
  {
    $this->input = new CharStream($line);
  }

  private function decodeValueUntilBoundary(bool $isKey): string
  {
    $i = $this->input;
    $value = '';
    $char = $i->take(1);

    if ($char === '"') {
      for (;;) {
        $char = $i->take(1);
        if ($char === '\\') {
          if ($i->peek(0) === 'n') {
            $value .= "\n";
            $i->move(1);
          } else {
            $value .= $i->take(1);
          }
        } else if ($char === '"') {
          break;
        } else {
          $value .= $char;
        }
      }
    } else {
      $value = $char;
      $boundary = $isKey ? '=' : ' ';
      while ( ! $i->isAtEnd()) {
        $char = $i->take(1);
        if ($char === $boundary) {
          break;
        }
        $value .= $char;
      }
      $i->move(-1);
    }

    return $value;
  }

  public function decode(): array
  {
    $values = [ ];
    while ( ! $this->input->isAtEnd()) {
      while ($this->input->peek(0) === ' ') {
        $this->input->move(1);
      }

      $key = $this->decodeValueUntilBoundary(true);
      $this->input->consume('=');
      $value = $this->decodeValueUntilBoundary(false);
      $values[$key] = $value;
    }
    return $values;
  }
}
