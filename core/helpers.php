<?php
/**
 * Helper utilities for Amethystclothing.lk
 * URL helpers, price formatting, CSRF, escaping, settings cache.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lang.php';

/* ------------------------------------------------------------------ */
/*  URL HELPERS                                                        */
/* ------------------------------------------------------------------ */

function base_url(string $path = ''): string
{
    $base = rtrim(env('APP_URL', 'http://localhost/amethystclothing'), '/');
    return $path === '' ? $base : $base . '/' . ltrim($path, '/');
}

function asset_url(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function current_path(): string
{
    return trim($_GET['route'] ?? '', '/');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/* ------------------------------------------------------------------ */
/*  ESCAPING & SECURITY                                                */
/* ------------------------------------------------------------------ */

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function verify_csrf(): bool
{
    return isset($_POST['_token']) && hash_equals($_SESSION['_csrf_token'] ?? '', $_POST['_token']);
}

/* ------------------------------------------------------------------ */
/*  MULTI-CURRENCY PRICE FORMATTING                                    */
/* ------------------------------------------------------------------ */

function get_currencies(): array
{
    static $currencies = null;
    if ($currencies !== null) return $currencies;

    $currencies = [];
    try {
        $rows = db()->query("SELECT code, symbol, exchange_rate, is_default FROM currencies WHERE is_active = 1")->fetchAll();
        foreach ($rows as $r) {
            $currencies[$r['code']] = $r;
        }
    } catch (Exception $e) {
        $currencies = [
            'LKR' => ['code' => 'LKR', 'symbol' => 'Rs.', 'exchange_rate' => 1, 'is_default' => 1],
        ];
    }
    return $currencies;
}

function current_currency(): string
{
    return $_SESSION['currency'] ?? 'LKR';
}

function format_price(float $amount, ?string $currency_code = null): string
{
    $code = $currency_code ?? current_currency();
    $currencies = get_currencies();
    $cur = $currencies[$code] ?? ['symbol' => 'Rs.', 'exchange_rate' => 1];

    $converted = $amount * (float) $cur['exchange_rate'];
    $symbol = $cur['symbol'];

    if ($code === 'LKR') {
        return $symbol . ' ' . number_format($converted, 2);
    }
    return $symbol . ' ' . number_format($converted, 2);
}

/* ------------------------------------------------------------------ */
/*  SLUG GENERATOR                                                     */
/* ------------------------------------------------------------------ */

function slugify(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/* ------------------------------------------------------------------ */
/*  SITE SETTINGS CACHE                                                */
/* ------------------------------------------------------------------ */

function get_all_settings(): array
{
    static $settings = null;
    if ($settings !== null) return $settings;

    $settings = [];
    try {
        $rows = db()->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
    } catch (Exception $e) {
        // Fail silently — settings table may not be seeded yet
    }
    return $settings;
}

function get_setting(string $key, ?string $default = null): ?string
{
    $settings = get_all_settings();
    return $settings[$key] ?? $default;
}

function get_announcement_text(): string
{
    return get_setting('announcement_bar_text', '');
}

/* ------------------------------------------------------------------ */
/*  FLASH MESSAGES                                                     */
/* ------------------------------------------------------------------ */

function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}
