<?php declare(strict_types=1);
namespace PN\Yaf\Utilities\Text;

abstract class Highlighter
{
  const BASE = [
    'mod_off'     => 20,
    'foreground'  => 30,
    'background'  => 40,
    'bright'      => 60,
  ];
  const COLORS = [
    'black'   => 0,
    'red'     => 1,
    'green'   => 2,
    'yellow'  => 3,
    'blue'    => 4,
    'magenta' => 5,
    'cyan'    => 6,
    'white'   => 7,
  ];
  const MODIFIERS = [
    'reset'     => 0,
    'bright'    => 1,
    'underline' => 4,
    'inverse'   => 7,
  ];

  private static function escapeSequence(array $mods): string
  {
    return "\x1b[" . implode(';', $mods) . 'm';
  }

  public static function wrapColor(string $line, array $config): string
  {
    if (array_key_exists('NO_COLOR', $_SERVER)) {
      return $line;
    }

    $mods = [ ];
    if ($config['bold'] ?? false) {
      $mods[] = self::MODIFIERS['bright'];
    }

    if ($foreground = $config['foreground'] ?? false) {
      if ( ! is_array($foreground)) {
        $foregroundColor = $foreground;
        $foreground = [ ];
      } else {
        $foregroundColor = $foreground[0] ?? $foreground['color'];
      }
      $mod = self::BASE['foreground'] + self::COLORS[$foregroundColor];
      if ($foreground['bright'] ?? false) {
        $mod += self::BASE['bright'];
      }
      $mods[] = $mod;
    }

    if ($background = $config['background'] ?? false) {
      if ( ! is_array($background)) {
        $backgroundColor = $background;
        $background = [ ];
      } else {
        $backgroundColor = $background[0] ?? $background['color'];
      }
      $mod = self::BASE['background'] + self::COLORS[$backgroundColor];
      if ($background['bright'] ?? false) {
        $mod += self::BASE['bright'];
      }
      $mods[] = $mod;
    }

    if ($config['underline'] ?? false) {
      $mods[] = self::MODIFIERS['underline'];
    }
    if ($config['inverse'] ?? false) {
      $mods[] = self::MODIFIERS['inverse'];
    }

    $line = self::escapeSequence($mods) . $line;
    if ($config['reset'] ?? true) {
      $line .= self::escapeSequence([self::MODIFIERS['reset']]);
    }
    return $line;
  }
}
