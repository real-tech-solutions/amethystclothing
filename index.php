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

// Currency switch via GET param
if (isset($_GET['currency']) && in_array($_GET['currency'], ['LKR', 'USD'])) {
    $_SESSION['currency'] = $_GET['currency'];
    // Redirect back without the param to keep URL clean
    $back = $_SERVER['HTTP_REFERER'] ?? base_url();
    // Don't redirect yet — base_url() needs helpers loaded first
}

// Load core
require_once __DIR__ . '/core/helpers.php';

// Handle currency redirect after helpers are loaded
if (isset($_GET['currency']) && in_array($_GET['currency'], ['LKR', 'USD'])) {
    $back = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $back);
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

// Resolve current request
$method = $_SERVER['REQUEST_METHOD'];
$uri    = trim($_GET['route'] ?? '', '/');
$router->resolve($method, $uri);
