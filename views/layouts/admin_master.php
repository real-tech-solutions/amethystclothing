<?php
/**
 * Amethystclothing.lk — Admin Master Layout
 * Sharp Minimalist Luxury UI — Strictly No AI Slop
 */

require_once __DIR__ . '/../../core/helpers.php';
require_admin_auth();

$adminUser = admin_user();
$currentRoute = current_path();

// Quick badge query for pending orders
$pendingOrdersCount = 0;
try {
    $pendingOrdersCount = (int) db()->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('placed', 'confirmed', 'printing_processing')")->fetchColumn();
} catch (Exception $e) {}

$pageTitle = $pageTitle ?? 'Admin Console // AMETHYST';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="<?= asset_url('images/icon_b.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('css/main.css') ?>">
    <style>
        :root {
            --admin-bg: #F4F2F8;
            --admin-surface: #FFFFFF;
            --admin-border: #E4E0EC;
            --admin-dark: #0B0910;
            --admin-amethyst: #4e2a84;
            --admin-amethyst-light: #9966cc;
            --admin-sidebar-w: 240px;
        }

        body.admin-body {
            background-color: var(--admin-bg);
            color: #120F18;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Bar */
        .admin-topbar {
            background-color: var(--admin-dark);
            color: #FFFFFF;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            border-bottom: 1px solid #1E1A26;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .admin-topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #FFFFFF;
        }

        .admin-brand img {
            height: 26px;
            width: auto;
            filter: brightness(0) invert(1);
        }

        .admin-brand-tag {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--admin-amethyst-light);
            background: rgba(78, 42, 132, 0.4);
            border: 1px solid rgba(153, 102, 204, 0.4);
            padding: 2px 8px;
        }

        .admin-topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #CCC8D8;
        }

        .admin-user-badge {
            font-family: 'Space Mono', monospace;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            background: #231E2C;
            color: #B5AFC2;
            padding: 2px 6px;
            border: 1px solid #332D40;
        }

        .admin-top-link {
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #B5AFC2;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color 0.15s;
        }

        .admin-top-link:hover {
            color: #FFFFFF;
        }

        .admin-logout-btn {
            background: transparent;
            border: 1px solid #332D40;
            color: #EF4444;
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 6px 12px;
            text-decoration: none;
            transition: all 0.15s;
        }

        .admin-logout-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: #EF4444;
        }

        /* Layout Container */
        .admin-layout-wrap {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 60px);
        }

        /* Sidebar */
        .admin-sidebar {
            width: var(--admin-sidebar-w);
            background: #0E0C14;
            border-right: 1px solid #1E1A26;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .admin-nav-group {
            padding: 20px 0;
        }

        .admin-nav-label {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #6A6478;
            padding: 8px 24px;
            display: block;
        }

        .admin-nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .admin-nav-item {
            margin-bottom: 2px;
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
            color: #A9A3B5;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.02em;
            border-left: 3px solid transparent;
            transition: all 0.15s;
        }

        .admin-nav-link:hover {
            color: #FFFFFF;
            background: rgba(255, 255, 255, 0.03);
        }

        .admin-nav-link.active {
            color: #FFFFFF;
            background: #181422;
            border-left-color: var(--admin-amethyst);
            font-weight: 600;
        }

        .admin-nav-badge {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            font-weight: 700;
            background: var(--admin-amethyst);
            color: #FFFFFF;
            padding: 2px 7px;
            border-radius: 0;
        }

        .admin-sidebar-footer {
            padding: 20px 24px;
            border-top: 1px solid #1E1A26;
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            color: #6A6478;
        }

        /* Main Content */
        .admin-main {
            flex: 1;
            padding: 32px 40px;
            overflow-y: auto;
        }

        .admin-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--admin-border);
        }

        .admin-breadcrumb {
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #7A7488;
            margin-bottom: 6px;
        }

        .admin-heading {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.02em;
            text-transform: uppercase;
            margin: 0;
            color: #0E0C14;
        }

        .admin-header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Metric Cards */
        .admin-metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .metric-card {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            padding: 24px;
            position: relative;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 3px;
            background: #E4E0EC;
        }

        .metric-card.accent-amethyst::before {
            background: var(--admin-amethyst);
        }

        .metric-card.accent-warning::before {
            background: #F59E0B;
        }

        .metric-card.accent-danger::before {
            background: #EF4444;
        }

        .metric-card.accent-success::before {
            background: #10B981;
        }

        .metric-label {
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #6B6578;
            margin-bottom: 12px;
        }

        .metric-value {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0E0C14;
            line-height: 1.1;
        }

        .metric-sub {
            font-size: 12px;
            color: #8C869B;
            margin-top: 10px;
        }

        /* Data Tables */
        .admin-card {
            background: var(--admin-surface);
            border: 1px solid var(--admin-border);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            margin-bottom: 32px;
        }

        .admin-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .admin-card-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #0E0C14;
            margin: 0;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }

        .admin-table th {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #6B6578;
            background: #FAF9FD;
            padding: 14px 20px;
            border-bottom: 1px solid var(--admin-border);
            font-weight: 700;
        }

        .admin-table td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--admin-border);
            color: #231E2C;
            vertical-align: middle;
        }

        .admin-table tbody tr:hover {
            background: #FBFBFE;
        }

        .admin-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Status Badges */
        .admin-status-badge {
            font-family: 'Space Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 3px 8px;
            display: inline-block;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .badge-placed {
            background: #FEF3C7;
            color: #92400E;
            border-color: #FDE68A;
        }

        .badge-confirmed {
            background: #DBEAFE;
            color: #1E40AF;
            border-color: #BFDBFE;
        }

        .badge-printing_processing {
            background: #EDE9FE;
            color: #5B21B6;
            border-color: #DDD6FE;
        }

        .badge-packed {
            background: #E0E7FF;
            color: #3730A3;
            border-color: #C7D2FE;
        }

        .badge-handed_to_courier {
            background: #FCE7F3;
            color: #9D174D;
            border-color: #FBCFE8;
        }

        .badge-delivered {
            background: #D1FAE5;
            color: #065F46;
            border-color: #A7F3D0;
        }

        .badge-cancelled {
            background: #FEE2E2;
            color: #991B1B;
            border-color: #FECACA;
        }

        .badge-instock {
            background: #D1FAE5;
            color: #065F46;
            border-color: #A7F3D0;
        }

        .badge-lowstock {
            background: #FEF3C7;
            color: #92400E;
            border-color: #FDE68A;
        }

        .badge-outofstock {
            background: #FEE2E2;
            color: #991B1B;
            border-color: #FECACA;
        }

        /* Buttons & Forms */
        .btn-admin {
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-weight: 700;
            padding: 8px 16px;
            border: 1px solid var(--admin-dark);
            background: var(--admin-dark);
            color: #FFFFFF;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
            border-radius: 0;
        }

        .btn-admin:hover {
            background: #231E2C;
            border-color: #231E2C;
        }

        .btn-admin-amethyst {
            background: var(--admin-amethyst);
            border-color: var(--admin-amethyst);
        }

        .btn-admin-amethyst:hover {
            background: #5e33a0;
            border-color: #5e33a0;
        }

        .btn-admin-outline {
            background: transparent;
            color: #231E2C;
            border-color: #D1CADB;
        }

        .btn-admin-outline:hover {
            background: #F4F2F8;
            border-color: #231E2C;
        }

        .btn-admin-sm {
            padding: 5px 10px;
            font-size: 10px;
        }

        /* Toast notifications */
        .admin-toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .admin-toast {
            background: #0E0C14;
            color: #FFFFFF;
            border-left: 4px solid var(--admin-amethyst);
            padding: 14px 20px;
            font-size: 13px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            min-width: 280px;
            animation: slideInRight 0.2s ease-out;
        }

        .admin-toast.toast-success {
            border-left-color: #10B981;
        }

        .admin-toast.toast-error {
            border-left-color: #EF4444;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 900px) {
            .admin-layout-wrap {
                flex-direction: column;
            }
            .admin-sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #1E1A26;
            }
            .admin-main {
                padding: 20px 16px;
            }
        }
    </style>
</head>
<body class="admin-body">
    <!-- Top Bar -->
    <header class="admin-topbar">
        <div class="admin-topbar-left">
            <a href="<?= base_url('admin/dashboard') ?>" class="admin-brand">
                <?php if (file_exists(__DIR__ . '/../../assets/images/logo_w-color.png')): ?>
                    <img src="<?= asset_url('images/logo_w-color.png') ?>" alt="Amethyst">
                <?php else: ?>
                    <span style="font-weight:800;letter-spacing:0.2em;font-size:16px;">AMETHYST</span>
                <?php endif; ?>
                <span class="admin-brand-tag">SYS // ADMIN</span>
            </a>
        </div>

        <div class="admin-topbar-right">
            <a href="<?= base_url() ?>" target="_blank" class="admin-top-link">
                <span>VIEW STOREFRONT</span> &nearr;
            </a>

            <div class="admin-user-info">
                <span><?= e($adminUser['name'] ?? 'Super Admin') ?></span>
                <span class="admin-user-badge"><?= e($adminUser['role'] ?? 'ADMIN') ?></span>
            </div>

            <a href="<?= base_url('admin/logout') ?>" class="admin-logout-btn">
                LOGOUT
            </a>
        </div>
    </header>

    <!-- App Body -->
    <div class="admin-layout-wrap">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-nav-group">
                <span class="admin-nav-label">Main Navigation</span>
                <ul class="admin-nav-list">
                    <li class="admin-nav-item">
                        <a href="<?= base_url('admin/dashboard') ?>" class="admin-nav-link <?= in_array($currentRoute, ['admin', 'admin/dashboard']) ? 'active' : '' ?>">
                            <span>Dashboard</span>
                            <span style="font-family:'Space Mono';font-size:11px;">01</span>
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a href="<?= base_url('admin/orders') ?>" class="admin-nav-link <?= $currentRoute === 'admin/orders' ? 'active' : '' ?>">
                            <span>Orders</span>
                            <?php if ($pendingOrdersCount > 0): ?>
                                <span class="admin-nav-badge"><?= $pendingOrdersCount ?></span>
                            <?php else: ?>
                                <span style="font-family:'Space Mono';font-size:11px;">02</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a href="<?= base_url('admin/products') ?>" class="admin-nav-link <?= $currentRoute === 'admin/products' ? 'active' : '' ?>">
                            <span>Inventory & Stock</span>
                            <span style="font-family:'Space Mono';font-size:11px;">03</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="admin-sidebar-footer">
                <div>AMETHYST SYSTEM v1.4</div>
                <div style="margin-top:4px;color:#454050;">STREETWEAR ARCHITECTURE</div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="admin-main">
            <?= $content ?>
        </main>
    </div>

    <!-- Toast Container -->
    <div id="admin-toast-container" class="admin-toast-container"></div>

    <script>
        // Global admin toast utility
        function showAdminToast(message, type = 'success') {
            const container = document.getElementById('admin-toast-container');
            const toast = document.createElement('div');
            toast.className = `admin-toast toast-${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }
    </script>
</body>
</html>
