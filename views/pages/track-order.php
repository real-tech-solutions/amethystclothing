<?php
/**
 * Real-Time Order Tracking Page — Amethystclothing.lk
 * Phase 3: Visual Progress Timeline (Placed -> Processing -> Packed -> Courier -> Delivered)
 */

$pageTitle = 'Track Live Order — Amethystclothing.lk';

$orderInput = trim($_GET['order'] ?? '');
$phoneInput = trim($_GET['phone'] ?? '');

$pdo = db();
$order = null;
$statusHistory = [];
$orderItems = [];

if (!empty($orderInput) || !empty($phoneInput)) {
    // Search by order_number or phone number
    if (!empty($orderInput) && !empty($phoneInput)) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND (customer_phone LIKE ? OR customer_phone = ?) ORDER BY id DESC LIMIT 1");
        $stmt->execute([$orderInput, '%' . $phoneInput, $phoneInput]);
    } elseif (!empty($orderInput)) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? OR customer_phone = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$orderInput, $orderInput]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_phone = ? OR customer_phone LIKE ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$phoneInput, '%' . $phoneInput]);
    }

    $order = $stmt->fetch();

    if ($order) {
        // Fetch status history
        $histStmt = $pdo->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY id ASC");
        $histStmt->execute([$order['id']]);
        $statusHistory = $histStmt->fetchAll();

        // Fetch order items
        $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
        $itemStmt->execute([$order['id']]);
        $orderItems = $itemStmt->fetchAll();
    }
}

// Stage progress map
$stageOrder = [
    'placed'               => 1,
    'confirmed'            => 2,
    'printing_processing'  => 2,
    'packed'               => 3,
    'handed_to_courier'    => 4,
    'out_for_delivery'     => 4,
    'delivered'            => 5,
    'cancelled'            => 0,
    'returned'             => 0,
];

$currentStageIndex = $order ? ($stageOrder[$order['order_status']] ?? 1) : 0;
?>
<section class="section track-section">
    <div class="container" style="max-width: 780px;">
        <!-- Header -->
        <div class="section-head section-head--center">
            <span class="section-eyebrow">REAL-TIME PRODUCTION & COURIER DISPATCH</span>
            <h1 class="section-title">TRACK YOUR SHIPMENT</h1>
            <p class="section-subtitle">Enter your Order Number (e.g. AME-2026-000101) or registered mobile number to view real-time delivery status.</p>
        </div>

        <!-- Tracking Search Form -->
        <div class="track-lookup-card">
            <form action="<?= base_url('track-order') ?>" method="GET" class="track-lookup-form">
                <div class="track-form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="track-order-num">ORDER NUMBER</label>
                        <input type="text" id="track-order-num" name="order" value="<?= e($orderInput) ?>" placeholder="AME-2026-XXXXXX" class="input">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="track-phone-num">MOBILE NUMBER (OPTIONAL)</label>
                        <input type="tel" id="track-phone-num" name="phone" value="<?= e($phoneInput) ?>" placeholder="077XXXXXXX" class="input">
                    </div>
                </div>
                <button type="submit" class="btn btn--primary btn--full" style="margin-top: 14px;">
                    TRACK LIVE STATUS &rarr;
                </button>
            </form>
        </div>

        <?php if ($order): ?>
            <!-- Order Status Card -->
            <div class="track-result-panel">
                <div class="track-result-panel__header">
                    <div>
                        <span class="track-badge">ORDER REFERENCE: <?= e($order['order_number']) ?></span>
                        <h3 class="track-order-title">STATUS: <?= strtoupper(str_replace('_', ' ', e($order['order_status']))) ?></h3>
                        <p class="track-order-date">Placed on <?= date('d M Y, h:i A', strtotime($order['ordered_at'])) ?></p>
                    </div>
                    <div class="track-result-barcode">
                        <span class="product-live-barcode">*<?= e($order['order_number']) ?>*</span>
                    </div>
                </div>

                <!-- Visual Vertical Tracking Timeline -->
                <div class="track-timeline">
                    <!-- Step 1: Placed -->
                    <div class="timeline-step <?= $currentStageIndex >= 1 ? 'is-completed' : '' ?> <?= $currentStageIndex === 1 ? 'is-current' : '' ?>">
                        <div class="timeline-step__marker">
                            <span class="timeline-step__bullet"></span>
                            <span class="timeline-step__line"></span>
                        </div>
                        <div class="timeline-step__content">
                            <h4 class="timeline-step__title">1. ORDER RECEIVED</h4>
                            <p class="timeline-step__desc">Your order has been recorded into our dispatch queue. Cash on Delivery scheduled.</p>
                            <?php if (!empty($statusHistory)): ?>
                                <span class="timeline-step__time"><?= date('d M Y, h:i A', strtotime($statusHistory[0]['created_at'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Step 2: Confirmed & Printing -->
                    <div class="timeline-step <?= $currentStageIndex >= 2 ? 'is-completed' : '' ?> <?= $currentStageIndex === 2 ? 'is-current' : '' ?>">
                        <div class="timeline-step__marker">
                            <span class="timeline-step__bullet"></span>
                            <span class="timeline-step__line"></span>
                        </div>
                        <div class="timeline-step__content">
                            <h4 class="timeline-step__title">2. VERIFIED & IN PRODUCTION</h4>
                            <p class="timeline-step__desc">Phone verified. Garments undergo high-density printing, inspection, and softening.</p>
                        </div>
                    </div>

                    <!-- Step 3: Packed -->
                    <div class="timeline-step <?= $currentStageIndex >= 3 ? 'is-completed' : '' ?> <?= $currentStageIndex === 3 ? 'is-current' : '' ?>">
                        <div class="timeline-step__marker">
                            <span class="timeline-step__bullet"></span>
                            <span class="timeline-step__line"></span>
                        </div>
                        <div class="timeline-step__content">
                            <h4 class="timeline-step__title">3. QC PASSED & PACKED</h4>
                            <p class="timeline-step__desc">100% Quality Checked. Tagged with authentic garment barcodes and sealed in weatherproof bag.</p>
                        </div>
                    </div>

                    <!-- Step 4: Out for Delivery -->
                    <div class="timeline-step <?= $currentStageIndex >= 4 ? 'is-completed' : '' ?> <?= $currentStageIndex === 4 ? 'is-current' : '' ?>">
                        <div class="timeline-step__marker">
                            <span class="timeline-step__bullet"></span>
                            <span class="timeline-step__line"></span>
                        </div>
                        <div class="timeline-step__content">
                            <h4 class="timeline-step__title">4. HANDED TO COURIER / OUT FOR DELIVERY</h4>
                            <p class="timeline-step__desc">Dispatched via partner courier service. Rider will call prior to doorstep arrival.</p>
                        </div>
                    </div>

                    <!-- Step 5: Delivered -->
                    <div class="timeline-step <?= $currentStageIndex >= 5 ? 'is-completed' : '' ?> <?= $currentStageIndex === 5 ? 'is-current' : '' ?>">
                        <div class="timeline-step__marker">
                            <span class="timeline-step__bullet"></span>
                        </div>
                        <div class="timeline-step__content">
                            <h4 class="timeline-step__title">5. DELIVERED & PAID</h4>
                            <p class="timeline-step__desc">Delivered to recipient. 14-Day size exchange window is now active.</p>
                        </div>
                    </div>
                </div>

                <!-- Order Details Snapshot -->
                <div class="track-order-details">
                    <div class="track-details-row">
                        <span>RECIPIENT:</span>
                        <strong><?= e($order['recipient_name']) ?> (<?= e($order['customer_phone']) ?>)</strong>
                    </div>
                    <div class="track-details-row">
                        <span>DELIVERY DESTINATION:</span>
                        <strong><?= e($order['shipping_city']) ?>, <?= e($order['shipping_district']) ?></strong>
                    </div>
                    <?php if (!empty($order['courier_partner'])): ?>
                        <div class="track-details-row">
                            <span>COURIER PARTNER:</span>
                            <strong style="color: var(--brand-deep);"><?= e($order['courier_partner']) ?></strong>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($order['courier_tracking_number'])): ?>
                        <div class="track-details-row">
                            <span>COURIER WAYBILL / TRACKING #:</span>
                            <strong style="font-family: var(--font-mono);"><?= e($order['courier_tracking_number']) ?></strong>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($statusHistory[0]['public_comment'])): ?>
                        <div class="track-details-row" style="background: rgba(78, 42, 132, 0.05); padding: 8px 12px; border-left: 3px solid var(--brand-deep); margin-top: 6px;">
                            <span>LATEST DISPATCH UPDATE:</span>
                            <span style="font-size: 13px; color: var(--text-primary);"><?= e($statusHistory[0]['public_comment']) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="track-details-row">
                        <span>TOTAL CASH DUE ON ARRIVAL:</span>
                        <strong style="color: var(--brand-deep); font-size: 16px;"><?= format_price((float)$order['grand_total']) ?></strong>
                    </div>
                </div>

                <!-- Action Links -->
                <div class="track-actions-row">
                    <a href="<?= base_url('invoice?order=' . urlencode($order['order_number'])) ?>" target="_blank" class="btn btn--outline btn--sm">
                        VIEW OFFICIAL INVOICE
                    </a>
                    <a href="https://wa.me/94770000000?text=<?= urlencode('Hi Amethyst! Inquiring about order ' . $order['order_number']) ?>" target="_blank" class="btn btn--primary btn--sm">
                        WHATSAPP DISPATCH HELPLINE
                    </a>
                </div>
            </div>

        <?php elseif (!empty($orderInput) || !empty($phoneInput)): ?>
            <!-- Not Found Notice -->
            <div class="track-not-found-box">
                <h3 style="font-family: var(--font-display); font-size: 18px; margin-bottom: 8px;">NO SHIPMENT FOUND</h3>
                <p style="font-family: var(--font-body); font-size: 14px; color: var(--text-secondary); margin-bottom: 16px;">
                    We couldn't find an order matching your search. Please double check your order number or phone number.
                </p>
                <p style="font-size: 13px; color: var(--text-muted);">Need immediate assistance? Message us on WhatsApp: <strong>+94 77 000 0000</strong></p>
            </div>
        <?php endif; ?>
    </div>
</section>
