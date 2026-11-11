<?php
/**
 * Checkout Success Page — Amethystclothing.lk
 * Displays order confirmation, itemized snapshot, COD reminder, and Print Invoice / Track Order links.
 */

$orderNumber = trim($_GET['order'] ?? '');
$pdo = db();

$stmt = $pdo->prepare("
    SELECT o.*, c.email AS customer_email, i.invoice_number 
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    LEFT JOIN invoices i ON o.id = i.order_id
    WHERE o.order_number = ?
    LIMIT 1
");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch();

$orderItems = [];
if ($order) {
    $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
    $itemStmt->execute([$order['id']]);
    $orderItems = $itemStmt->fetchAll();
}

$pageTitle = $order ? 'Order Confirmed: ' . $order['order_number'] . ' — Amethystclothing.lk' : 'Order Success — Amethystclothing.lk';
?>
<section class="section checkout-success-section">
    <div class="container" style="max-width: 860px;">
        <?php if ($order): ?>
            <!-- Success Banner -->
            <div class="success-banner">
                <div class="success-banner__icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <span class="success-banner__eyebrow">ORDER CONFIRMED // CASH ON DELIVERY</span>
                <h1 class="success-banner__title">THANK YOU FOR YOUR ORDER!</h1>
                <p class="success-banner__desc">
                    Your order <strong><?= e($order['order_number']) ?></strong> has been placed into our system. Our team will verify your phone number and dispatch your package shortly.
                </p>
                <div class="success-barcode-box">
                    <span class="product-live-barcode">*<?= e($order['order_number']) ?>*</span>
                    <span class="barcode-legend-text">ORDER NO: <?= e($order['order_number']) ?></span>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="success-actions-bar">
                <a href="<?= base_url('invoice?order=' . urlencode($order['order_number'])) ?>" target="_blank" class="btn btn--primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    PRINT OFFICIAL INVOICE
                </a>
                <a href="<?= base_url('track-order?order=' . urlencode($order['order_number'])) ?>" class="btn btn--outline">
                    TRACK LIVE STATUS &rarr;
                </a>
            </div>

            <!-- Summary Details Grid -->
            <div class="order-summary-box">
                <div class="order-summary-box__header">
                    <span class="header-tag">DELIVERY & PAYMENT DETAILS</span>
                    <span class="header-status">STATUS: <?= strtoupper(e($order['order_status'])) ?></span>
                </div>

                <div class="order-info-grid">
                    <div class="info-block">
                        <span class="info-label">RECIPIENT NAME:</span>
                        <strong class="info-value"><?= e($order['recipient_name']) ?></strong>
                    </div>
                    <div class="info-block">
                        <span class="info-label">PHONE NUMBER:</span>
                        <strong class="info-value"><?= e($order['customer_phone']) ?></strong>
                    </div>
                    <div class="info-block">
                        <span class="info-label">DELIVERY ADDRESS:</span>
                        <strong class="info-value">
                            <?= e($order['shipping_address_line1']) ?>
                            <?= !empty($order['shipping_address_line2']) ? ', ' . e($order['shipping_address_line2']) : '' ?>,
                            <?= e($order['shipping_city']) ?>, <?= e($order['shipping_district']) ?>
                        </strong>
                    </div>
                    <div class="info-block">
                        <span class="info-label">PAYMENT METHOD:</span>
                        <strong class="info-value">Cash on Delivery (COD)</strong>
                    </div>
                </div>

                <!-- Items Ordered Table -->
                <div class="order-items-wrapper">
                    <h4 class="items-table-title">ITEMS ORDERED (<?= count($orderItems) ?>)</h4>
                    <table class="ordered-items-table">
                        <thead>
                            <tr>
                                <th>PRODUCT</th>
                                <th>SKU</th>
                                <th>SIZE</th>
                                <th>QTY</th>
                                <th style="text-align: right;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td><strong><?= e($item['product_name_snapshot']) ?></strong></td>
                                    <td><?= e($item['sku_snapshot']) ?></td>
                                    <td><span class="size-badge"><?= e($item['size_snapshot']) ?></span></td>
                                    <td><?= (int)$item['quantity'] ?></td>
                                    <td style="text-align: right;"><?= format_price((float)$item['line_total']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">SUBTOTAL:</td>
                                <td style="text-align: right;"><?= format_price((float)$order['subtotal_amount']) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4">SHIPPING FEE:</td>
                                <td style="text-align: right;"><?= format_price((float)$order['shipping_fee']) ?></td>
                            </tr>
                            <tr class="grand-total-row">
                                <td colspan="4"><strong>AMOUNT DUE (CASH ON DELIVERY):</strong></td>
                                <td style="text-align: right;"><strong><?= format_price((float)$order['grand_total']) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Support Helpline -->
            <div class="success-footer-note">
                <p>Need to update your address or have questions? Contact our WhatsApp helpline at <a href="https://wa.me/94770000000" target="_blank" rel="noopener"><strong>+94 77 000 0000</strong></a>.</p>
                <a href="<?= base_url('shop') ?>" class="btn btn--outline btn--sm" style="margin-top: 16px;">CONTINUE SHOPPING</a>
            </div>

        <?php else: ?>
            <div style="text-align: center; padding: 80px 20px;">
                <h2 style="font-family: var(--font-display); font-size: 28px; text-transform: uppercase;">ORDER NOT FOUND</h2>
                <p style="color: var(--text-secondary); margin: 12px 0 24px;">No order matches the requested reference.</p>
                <a href="<?= base_url('shop') ?>" class="btn btn--primary">RETURN TO SHOP</a>
            </div>
        <?php endif; ?>
    </div>
</section>
