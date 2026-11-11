<?php
/**
 * Official Printable Invoice — Amethystclothing.lk
 * Phase 3: High-end Minimalist Garment Invoice styled for print & screen
 * Features Libre Barcode 39 Text strictly for the official Order Number Barcode
 */

$orderNumber = trim($_GET['order'] ?? '');
$pdo = db();

$stmt = $pdo->prepare("
    SELECT o.*, c.email AS customer_email, i.invoice_number, i.generated_at AS invoice_date
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

if (!$order) {
    die("<h1>Invoice Not Found</h1><p>No order found matching " . htmlspecialchars($orderNumber) . "</p>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?= e($order['order_number']) ?> — Amethystclothing.lk</title>
    <link rel="icon" type="image/png" href="<?= asset_url('images/icon_b.png') ?>">

    <!-- Google Fonts: Archivo Black, Archivo Narrow, Archivo, Libre Barcode 39 Text -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Archivo+Narrow:wght@400;600;700&family=Archivo:wght@400;600;700&family=Libre+Barcode+39+Text&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            border-radius: 0 !important;
        }

        body {
            font-family: 'Archivo', sans-serif;
            background-color: #F7F5FA;
            color: #0D0B12;
            line-height: 1.4;
            padding: 40px 20px;
        }

        .invoice-actions {
            max-width: 820px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-print {
            background-color: #0B0910;
            color: #FFFFFF;
            padding: 10px 24px;
            font-family: 'Archivo Narrow', sans-serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            border: 1px solid #0B0910;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print:hover {
            background-color: #4e2a84;
            border-color: #4e2a84;
        }

        .btn-back {
            color: #5A5565;
            font-family: 'Archivo Narrow', sans-serif;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            text-decoration: none;
        }

        .btn-back:hover {
            color: #0D0B12;
        }

        .invoice-sheet {
            max-width: 820px;
            margin: 0 auto;
            background: #FFFFFF;
            border: 1px solid #E6E2EE;
            padding: 48px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
        }

        /* Top Barcode Header */
        .invoice-top-barcode {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 2px solid #0D0B12;
            padding-bottom: 16px;
            margin-bottom: 32px;
        }

        .invoice-logo img {
            height: 42px;
            width: auto;
        }

        .invoice-barcode-block {
            text-align: right;
        }

        .invoice-barcode-font {
            font-family: 'Libre Barcode 39 Text', cursive;
            font-size: 46px;
            line-height: 1;
            letter-spacing: 0.04em;
            color: #0D0B12;
            display: inline-block;
        }

        .invoice-barcode-sub {
            display: block;
            font-family: 'Archivo Narrow', sans-serif;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            color: #5A5565;
            margin-top: 4px;
        }

        /* Meta Grid */
        .invoice-meta-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 32px;
            border-bottom: 1px solid #E6E2EE;
            padding-bottom: 28px;
            margin-bottom: 28px;
        }

        .meta-col__title {
            font-family: 'Archivo Narrow', sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            color: #4e2a84;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .meta-col__content {
            font-size: 13px;
            line-height: 1.6;
        }

        .meta-col__content strong {
            font-size: 15px;
            display: block;
            margin-bottom: 4px;
        }

        .invoice-details-box {
            background-color: #F7F5FA;
            border: 1px solid #E6E2EE;
            padding: 16px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-family: 'Archivo Narrow', sans-serif;
            font-size: 12px;
        }

        .detail-row span {
            color: #5A5565;
            font-weight: 600;
        }

        .detail-row strong {
            color: #0D0B12;
            font-weight: 700;
        }

        /* Table of Items */
        .invoice-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
            font-size: 13px;
        }

        .invoice-items-table th {
            font-family: 'Archivo Narrow', sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            text-align: left;
            background-color: #0B0910;
            color: #FFFFFF;
            padding: 10px 14px;
        }

        .invoice-items-table td {
            padding: 14px;
            border-bottom: 1px solid #E6E2EE;
        }

        .invoice-items-table tr:last-child td {
            border-bottom: 2px solid #0D0B12;
        }

        .item-sku {
            font-family: 'Archivo Narrow', sans-serif;
            font-size: 11px;
            color: #898494;
            margin-top: 3px;
            display: block;
        }

        .item-size-tag {
            font-family: 'Archivo Narrow', sans-serif;
            font-size: 11px;
            font-weight: 700;
            background: #F7F5FA;
            border: 1px solid #E6E2EE;
            padding: 2px 6px;
        }

        /* Financials Table */
        .invoice-totals-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 36px;
        }

        .invoice-totals-table {
            width: 320px;
            border-collapse: collapse;
        }

        .invoice-totals-table td {
            padding: 6px 0;
            font-family: 'Archivo Narrow', sans-serif;
            font-size: 13px;
        }

        .invoice-totals-table .total-label {
            color: #5A5565;
            font-weight: 600;
        }

        .invoice-totals-table .total-val {
            text-align: right;
            font-weight: 700;
        }

        .invoice-totals-table .grand-row td {
            border-top: 1px solid #0D0B12;
            padding-top: 10px;
            font-size: 16px;
        }

        .invoice-totals-table .grand-row .total-val {
            font-size: 18px;
            color: #4e2a84;
        }

        /* Footer & Stamp */
        .invoice-footer {
            border-top: 1px solid #E6E2EE;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Archivo Narrow', sans-serif;
            font-size: 11px;
            color: #5A5565;
            letter-spacing: 0.06em;
        }

        .invoice-footer strong {
            color: #0D0B12;
        }

        /* PRINT STYLES */
        @media print {
            body {
                background: #FFFFFF;
                padding: 0;
            }

            .invoice-actions {
                display: none;
            }

            .invoice-sheet {
                border: none;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-actions">
        <a href="<?= base_url('track-order?order=' . urlencode($order['order_number'])) ?>" class="btn-back">&larr; Back to Order Tracking</a>
        <button type="button" class="btn-print" onclick="window.print()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Print / Save Invoice
        </button>
    </div>

    <div class="invoice-sheet">
        <!-- Top Barcode Section -->
        <div class="invoice-top-barcode">
            <div class="invoice-logo">
                <img src="<?= asset_url('images/logo_b-color.png') ?>" alt="AMETHYST" width="180" height="42">
                <p style="font-family: 'Archivo Narrow', sans-serif; font-size: 11px; font-weight: 700; color: #5A5565; letter-spacing: 0.08em; margin-top: 6px;">
                    PREMIUM T-SHIRT PRINTING & EXCLUSIVE STREETWEAR
                </p>
            </div>
            <div class="invoice-barcode-block">
                <!-- Barcode Font strictly for official Invoice Order Number -->
                <span class="invoice-barcode-font">*<?= e($order['order_number']) ?>*</span>
                <span class="invoice-barcode-sub"><?= e($order['order_number']) ?> // SRI LANKA ORIGINAL</span>
            </div>
        </div>

        <!-- Meta Grid -->
        <div class="invoice-meta-grid">
            <div class="meta-col">
                <h4 class="meta-col__title">DELIVERY RECIPIENT</h4>
                <div class="meta-col__content">
                    <strong><?= e($order['recipient_name']) ?></strong>
                    <p><?= e($order['shipping_address_line1']) ?><?= !empty($order['shipping_address_line2']) ? ', ' . e($order['shipping_address_line2']) : '' ?></p>
                    <p><?= e($order['shipping_city']) ?>, <?= e($order['shipping_district']) ?>, Sri Lanka</p>
                    <p>Phone: <?= e($order['customer_phone']) ?></p>
                    <?php if (!empty($order['customer_email'])): ?>
                        <p>Email: <?= e($order['customer_email']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="meta-col">
                <div class="invoice-details-box">
                    <div class="detail-row">
                        <span>INVOICE NUMBER:</span>
                        <strong><?= e($order['invoice_number'] ?? 'INV-' . substr($order['order_number'], 4)) ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>ORDER DATE:</span>
                        <strong><?= date('d M Y, h:i A', strtotime($order['ordered_at'])) ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>PAYMENT TERMS:</span>
                        <strong>CASH ON DELIVERY</strong>
                    </div>
                    <div class="detail-row">
                        <span>ORDER STATUS:</span>
                        <strong style="color: #15803D;"><?= strtoupper(e($order['order_status'])) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ordered Items Table -->
        <table class="invoice-items-table">
            <thead>
                <tr>
                    <th style="width: 45%;">ITEM DESCRIPTION</th>
                    <th style="width: 20%;">SIZE / COLOUR</th>
                    <th style="width: 15%; text-align: center;">QTY</th>
                    <th style="width: 20%; text-align: right;">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td>
                            <strong><?= e($item['product_name_snapshot']) ?></strong>
                            <span class="item-sku">SKU: <?= e($item['sku_snapshot']) ?></span>
                        </td>
                        <td>
                            <span class="item-size-tag"><?= e($item['size_snapshot']) ?></span>
                            <?php if (!empty($item['color_snapshot'])): ?>
                                <span style="font-size: 11px; color: #5A5565; margin-left: 6px;"><?= e($item['color_snapshot']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center; font-weight: 700;"><?= (int)$item['quantity'] ?></td>
                        <td style="text-align: right; font-weight: 700;"><?= format_price((float)$item['line_total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals Table -->
        <div class="invoice-totals-wrapper">
            <table class="invoice-totals-table">
                <tr>
                    <td class="total-label">SUBTOTAL:</td>
                    <td class="total-val"><?= format_price((float)$order['subtotal_amount']) ?></td>
                </tr>
                <tr>
                    <td class="total-label">SHIPPING (<?= e($order['shipping_district']) ?>):</td>
                    <td class="total-val"><?= format_price((float)$order['shipping_fee']) ?></td>
                </tr>
                <tr class="grand-row">
                    <td class="total-label">TOTAL (CASH DUE):</td>
                    <td class="total-val"><?= format_price((float)$order['grand_total']) ?></td>
                </tr>
            </table>
        </div>

        <!-- Footer Notes -->
        <div class="invoice-footer">
            <div>
                <strong>AMETHYSTCLOTHING.LK</strong> &bull; Colombo, Sri Lanka &bull; +94 77 000 0000
            </div>
            <div>
                14-Day Size Exchange Guaranteed &bull; Customer Copy
            </div>
        </div>
    </div>
</body>
</html>
