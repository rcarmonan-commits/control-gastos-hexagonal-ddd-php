<?php

declare(strict_types=1);

final class EnvLoader
{
    private static bool $loaded = false;

    public static function load(?string $envPath = null): void
    {
        if (self::$loaded) {
            return;
        }

        $basePath = $envPath ?? dirname(__DIR__) . '/.env';
        if (!is_file($basePath) || !is_readable($basePath)) {
            self::$loaded = true;
            return;
        }

        $lines = file($basePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            self::$loaded = true;
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $separatorPos = strpos($trimmed, '=');
            if ($separatorPos === false) {
                continue;
            }

            $key = trim(substr($trimmed, 0, $separatorPos));
            $value = trim(substr($trimmed, $separatorPos + 1));

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            if ($key !== '' && getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        self::load();
        $envVal = getenv($key);
        if ($envVal !== false) {
            return (string) $envVal;
        }
        if (isset($_ENV[$key])) {
            return (string) $_ENV[$key];
        }
        return $default;
    }
}
