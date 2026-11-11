<?php
/**
 * Amethystclothing.lk — Admin Dashboard
 * High-impact metrics, low-stock inventory alerts, recent orders overview.
 */

require_once __DIR__ . '/../../../core/helpers.php';
require_admin_auth();

$pageTitle = 'Dashboard // AMETHYST ADMIN';

// ── 1. Fetch Metrics ──
// Total Revenue (exclude cancelled)
$totalRevenue = 0.0;
try {
    $totalRevenue = (float) db()->query("SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE order_status != 'cancelled'")->fetchColumn();
} catch (Exception $e) {}

// Pending Orders Count
$pendingCount = 0;
try {
    $pendingCount = (int) db()->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('placed', 'confirmed', 'printing_processing')")->fetchColumn();
} catch (Exception $e) {}

// Total Orders Count
$totalOrders = 0;
try {
    $totalOrders = (int) db()->query("SELECT COUNT(*) FROM orders")->fetchColumn();
} catch (Exception $e) {}

// Low Stock Variants Count (stock <= 5)
$lowStockCount = 0;
try {
    $lowStockCount = (int) db()->query("SELECT COUNT(*) FROM product_variants WHERE total_stock_quantity <= 5 AND is_active = 1")->fetchColumn();
} catch (Exception $e) {}

// ── 2. Fetch Low Stock Variants (Up to 6 items) ──
$lowStockVariants = [];
try {
    $stmt = db()->query("
        SELECT pv.id, pv.sku, pv.color_name, pv.size_code, pv.total_stock_quantity, p.name as product_name
        FROM product_variants pv
        JOIN products p ON p.id = pv.product_id
        WHERE pv.total_stock_quantity <= 5 AND pv.is_active = 1
        ORDER BY pv.total_stock_quantity ASC
        LIMIT 6
    ");
    $lowStockVariants = $stmt->fetchAll();
} catch (Exception $e) {}

// ── 3. Fetch Recent Orders (Up to 8 orders) ──
$recentOrders = [];
try {
    $stmt = db()->query("
        SELECT id, order_number, recipient_name, customer_phone, grand_total, payment_status, order_status, ordered_at
        FROM orders
        ORDER BY ordered_at DESC
        LIMIT 8
    ");
    $recentOrders = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<!-- Header -->
<div class="admin-page-header">
    <div>
        <div class="admin-breadcrumb">SYSTEM // COMMAND CENTER</div>
        <h1 class="admin-heading">Operations Dashboard</h1>
    </div>
    <div class="admin-header-actions">
        <a href="<?= base_url('admin/orders') ?>" class="btn-admin btn-admin-outline">
            VIEW ALL ORDERS &rarr;
        </a>
        <a href="<?= base_url('admin/products') ?>" class="btn-admin btn-admin-amethyst">
            MANAGE INVENTORY &rarr;
        </a>
    </div>
</div>

<!-- 3 Key Metric Cards -->
<div class="admin-metrics-grid">
    <!-- Total Revenue -->
    <div class="metric-card accent-amethyst">
        <div class="metric-label">TOTAL REVENUE (SALES)</div>
        <div class="metric-value">Rs. <?= number_format($totalRevenue, 2) ?></div>
        <div class="metric-sub">Across <?= $totalOrders ?> total orders received</div>
    </div>

    <!-- Pending Orders -->
    <div class="metric-card accent-warning">
        <div class="metric-label">PENDING FULFILLMENT</div>
        <div class="metric-value"><?= $pendingCount ?></div>
        <div class="metric-sub">Requires verification, printing or dispatch</div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="metric-card <?= $lowStockCount > 0 ? 'accent-danger' : 'accent-success' ?>">
        <div class="metric-label">LOW STOCK ALERTS (&le; 5 UNITS)</div>
        <div class="metric-value"><?= $lowStockCount ?></div>
        <div class="metric-sub">Variants needing urgent restock</div>
    </div>
</div>

<div style="display:grid;grid-template-columns: 2fr 1fr;gap:24px;align-items:start;">
    <!-- Recent Orders Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Recent Orders</h2>
            <a href="<?= base_url('admin/orders') ?>" class="btn-admin btn-admin-outline btn-admin-sm">ALL ORDERS</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date & Time</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;padding:32px;color:#8C869B;">
                                No orders received yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $ord): ?>
                            <tr>
                                <td>
                                    <strong style="font-family:'Space Mono';font-size:12px;letter-spacing:0.05em;"><?= e($ord['order_number']) ?></strong>
                                </td>
                                <td style="font-size:12px;color:#6B6578;">
                                    <?= date('M d, Y h:i A', strtotime($ord['ordered_at'])) ?>
                                </td>
                                <td>
                                    <div style="font-weight:600;font-size:13px;"><?= e($ord['recipient_name']) ?></div>
                                    <div style="font-family:'Space Mono';font-size:11px;color:#716B80;"><?= e($ord['customer_phone']) ?></div>
                                </td>
                                <td>
                                    <strong>Rs. <?= number_format($ord['grand_total'], 2) ?></strong>
                                </td>
                                <td>
                                    <span class="admin-status-badge badge-<?= e($ord['order_status']) ?>">
                                        <?= strtoupper(str_replace('_', ' ', $ord['order_status'])) ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <a href="<?= base_url('admin/orders?order=' . urlencode($ord['order_number'])) ?>" class="btn-admin btn-admin-outline btn-admin-sm">
                                        MANAGE
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Inventory Alerts -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Low Stock Alerts</h2>
            <a href="<?= base_url('admin/products') ?>" class="btn-admin btn-admin-amethyst btn-admin-sm">RESTOCK</a>
        </div>
        <div>
            <?php if (empty($lowStockVariants)): ?>
                <div style="text-align:center;padding:32px 16px;color:#065F46;">
                    <div style="font-size:24px;margin-bottom:8px;">&check;</div>
                    <div style="font-weight:700;font-size:13px;">INVENTORY HEALTHY</div>
                    <div style="font-size:12px;color:#6B6578;margin-top:4px;">No active variants are below threshold (&le; 5).</div>
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Item / SKU</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockVariants as $var): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:600;font-size:12px;"><?= e($var['product_name']) ?></div>
                                    <div style="font-family:'Space Mono';font-size:10px;color:#716B80;">
                                        <?= e($var['sku']) ?> &bull; <?= e($var['color_name']) ?> / <?= e($var['size_code']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($var['total_stock_quantity'] <= 0): ?>
                                        <span class="admin-status-badge badge-outofstock">OUT (0)</span>
                                    <?php else: ?>
                                        <span class="admin-status-badge badge-lowstock"><?= (int)$var['total_stock_quantity'] ?> LEFT</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
