<?php
/**
 * Lightweight .env parser for Amethystclothing.lk
 * Loads environment variables from .env file into $_ENV and $_SERVER
 */

(function () {
    $envPath = __DIR__ . '/../.env';
    if (!file_exists($envPath)) return;

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        // Strip surrounding quotes
        if (preg_match('/^"(.*)"$/', $value, $m)) $value = $m[1];
        elseif (preg_match("/^'(.*)'$/", $value, $m)) $value = $m[1];

        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
        putenv("$key=$value");
    }
})();

/**
 * Retrieve an environment variable with an optional default.
 */
function env(string $key, $default = null)
{
    return $_ENV[$key] ?? $default;
}
