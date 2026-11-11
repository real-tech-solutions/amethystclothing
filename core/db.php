<?php
/**
 * Singleton PDO database connection for Amethystclothing.lk
 * Uses utf8mb4, strict error mode, assoc fetch by default.
 */

require_once __DIR__ . '/env.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host   = env('DB_HOST', '127.0.0.1');
    $port   = env('DB_PORT', '3306');
    $dbname = env('DB_NAME', 'amethystclothing_db');
    $user   = env('DB_USER', 'root');
    $pass   = env('DB_PASS', '');

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        if (env('APP_ENV') === 'local') {
            die('<h1>Database Connection Failed</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>');
        }
        die('<h1>Service Unavailable</h1><p>Please try again later.</p>');
    }

    return $pdo;
}
