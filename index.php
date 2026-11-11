<?php
/**
 * Amethystclothing.lk — Front Controller
 * All non-static requests are routed through this entry point via .htaccess
 */

session_start();

// Default currency
if (!isset($_SESSION['currency'])) {
    $_SESSION['currency'] = 'LKR';
}

// Default language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

// Currency switch via GET param
if (isset($_GET['currency']) && in_array($_GET['currency'], ['LKR', 'USD'])) {
    $_SESSION['currency'] = $_GET['currency'];
}

// Language switch via GET param
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'si'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Load core
require_once __DIR__ . '/core/helpers.php';

// Handle param redirects to clean URL if needed
if ((isset($_GET['currency']) && in_array($_GET['currency'], ['LKR', 'USD'])) || 
    (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'si']))) {
    // Preserve other query params if any, or redirect cleanly
    $params = $_GET;
    unset($params['route'], $params['currency'], $params['lang']);
    $base = strtok($_SERVER['REQUEST_URI'], '?');
    $query = http_build_query($params);
    $target = $base . ($query ? '?' . $query : '');
    header('Location: ' . $target);
    exit;
}

// Initialize Router
require_once __DIR__ . '/core/router.php';
$router = new Router();

// ── Public Storefront Routes ──
$router->get('',                    __DIR__ . '/views/pages/home.php');
$router->get('shop',               __DIR__ . '/views/pages/shop.php');
$router->get('category/{slug}',    __DIR__ . '/views/pages/category.php');
$router->get('product/{slug}',     __DIR__ . '/views/pages/product.php');
$router->get('track-order',        __DIR__ . '/views/pages/track-order.php');
$router->get('checkout',           __DIR__ . '/views/pages/checkout.php');
$router->get('checkout-success',   __DIR__ . '/views/pages/checkout-success.php');
$router->get('invoice',            __DIR__ . '/views/pages/invoice.php');

// Resolve current request
$method = $_SERVER['REQUEST_METHOD'];
$uri    = trim($_GET['route'] ?? '', '/');
$router->resolve($method, $uri);
