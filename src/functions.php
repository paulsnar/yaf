<?php declare(strict_types=1);

namespace PN\Yaf {
  function str_starts_with(string $haystack, string $needle): bool {
    if ($needle === '') {
      return false;
    }
    return strpos($haystack, $needle) === 0;
  }

  function str_ends_with(string $haystack, string $needle): bool {
    if ($needle === '') {
      return false;
    }
    return strrpos($haystack, $needle) === strlen($haystack) - strlen($needle);
  }

  function str_maskpos(string $haystack, string $mask): ?int {
    if ($mask === '') {
      return null;
    }

    $min = INF;
    foreach (str_split($mask) as $char) {
      $pos = strpos($haystack, $char);
      if ($pos !== false && $pos < $min) {
        $min = $pos;
        $haystack = substr($haystack, 0, $min);
      }
    }
    if ($min === INF) {
      return null;
    }
    return $min;
  }

  function path_join(string ...$parts): string {
    return implode(DIRECTORY_SEPARATOR, $parts);
  }

  function array_zip(array ...$arrays): array {
    if (count($arrays) === 0) {
      return [ ];
    }

    $tuples = [ ];
    $i = 0;
    $lengths = array_map('count', $arrays);
    for (;;) {
      $tuple = [ ];
      foreach ($arrays as $n => $array) {
        if ($lengths[$n] <= $i) {
          return $tuples;
        }
        $tuple[] = $array[$i];
      }
      $tuples[] = $tuple;
      $i += 1;
    }
  }

  function array_pluck(array $array, ...$keys) {
    $plucked = [ ];
    foreach ($keys as $key) {
      if (array_key_exists($key, $array)) {
        $plucked[$key] = $array[$key];
      }
    }
    return $plucked;
  }

  function array_omit(array $array, ...$keys) {
    foreach ($keys as $key) {
      if (array_key_exists($key, $array)) {
        unset($array[$key]);
      }
    }
    return $array;
  }

  function vector_map(callable $callback, array $array): array {
    $result = [ ];
    $i = 0;
    foreach ($array as $key => $value) {
      $item = $callback($value, $key, $i);
      if ($item instanceof \Generator) {
        $result[$item->key()] = $item->current();
        do {
          $item->next();
        } while ($item->valid());
      } else {
        $result[$key] = $item;
      }
      $i += 1;
    }
    return $result;
  }

  function json_decode(string $json) {
    $flags = 0;
    if (defined('\\JSON_THROW_ON_ERROR')) {
      $flags |= constant('\\JSON_THROW_ON_ERROR');
    }
    $value = \json_decode($json, true, $flags);
    if ($value === null && json_last_error() !== JSON_ERROR_NONDE) {
      throw new \RuntimeException(json_last_error_msg());
    }
    return $value;
  }

  function json_encode($thing, int $flags = 0): string {
    $flags |= JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;
    $encoded = \json_encode($thing, $flags);
    if ($encoded === false) {
      throw new \RuntimeException(json_last_error_msg());
    }
    return $encoded;
  }
}

namespace PN\Yaf\Debug {
  function format_backtrace(array $backtrace): string {
    $formatted = '';
    foreach ($backtrace as $level => $frame) {
      $call = ($frame['class'] ?? false) ?
        "{$frame['class']}{$frame['type']}{$frame['function']}" :
        $frame['function'];

      // this happens with anonymous classes
      if (strpos($call, "\0") !== false) {
        $call = str_replace("\0", "\u{2400}", $call);
      }

      $formatted .= sprintf("%2d: %s (at %s:%d)\n",
        $level, $call, $frame['file'] ?? '<unknown>', $frame['line'] ?? 0);
    }
    return $formatted;
  }

  function callsite(): string {
    $bt = debug_backtrace(0);
    $site = $bt[1];
    $call = $bt[2];
    $call = ($call['class'] ?? false) ?
      "{$call['class']}{$call['type']}{$call['function']}" :
      $call['function'];
    return sprintf("%s (at %s:%d)",
      $call, $site['file'] ?? '<unknown>', $site['line'] ?? 0);
  }
}
