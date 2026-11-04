<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap file.
 *
 * Loads the Composer autoloader and environment configuration for tests.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load environment variables from .env.test if it exists (for integration tests)
$envTestFile = dirname(__DIR__) . '/.env.test';
if (file_exists($envTestFile)) {
    $lines = file($envTestFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            // Remove surrounding quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
        }
    }
}
