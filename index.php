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
$router->get('categories',         __DIR__ . '/views/pages/categories.php');
$router->get('category/{slug}',    __DIR__ . '/views/pages/category.php');
$router->get('product/{slug}',     __DIR__ . '/views/pages/product.php');
$router->get('about',              __DIR__ . '/views/pages/about.php');
$router->get('support',            __DIR__ . '/views/pages/support.php');
$router->get('contact',            __DIR__ . '/views/pages/contact.php');
$router->get('track-order',        __DIR__ . '/views/pages/track-order.php');
$router->get('checkout',           __DIR__ . '/views/pages/checkout.php');
$router->get('checkout-success',   __DIR__ . '/views/pages/checkout-success.php');
$router->get('invoice',            __DIR__ . '/views/pages/invoice.php');
$router->get('wishlist',           __DIR__ . '/views/pages/wishlist.php');
$router->get('auth',               __DIR__ . '/views/pages/auth.php');
$router->get('account',            __DIR__ . '/views/pages/account/dashboard.php');
$router->get('account/dashboard',  __DIR__ . '/views/pages/account/dashboard.php');
$router->get('account/logout',     __DIR__ . '/views/pages/account/logout.php');

// ── Admin Panel Routes ──
$router->get('admin',              __DIR__ . '/views/pages/admin/dashboard.php');
$router->get('admin/login',        __DIR__ . '/views/pages/admin/login.php');
$router->get('admin/dashboard',    __DIR__ . '/views/pages/admin/dashboard.php');
$router->get('admin/orders',       __DIR__ . '/views/pages/admin/orders.php');
$router->get('admin/products',     __DIR__ . '/views/pages/admin/products.php');
$router->get('admin/categories',   __DIR__ . '/views/pages/admin/categories.php');
$router->get('admin/size-charts',  __DIR__ . '/views/pages/admin/size_charts.php');
$router->get('admin/reviews',      __DIR__ . '/views/pages/admin/reviews.php');
$router->get('admin/logout',       __DIR__ . '/views/pages/admin/logout.php');

// Resolve current request
$method = $_SERVER['REQUEST_METHOD'];
$uri    = trim($_GET['route'] ?? '', '/');
$router->resolve($method, $uri);
