<?php
/**
 * AJAX Checkout Processor — Amethystclothing.lk
 * Single-Page 60-Second Guest Checkout Processing
 * Handles Customer Lookup/Create, Stock Validation, Transaction, Stock Deduction, and Invoice Generation.
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../core/helpers.php';

// Validate cart is not empty
if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Your shopping bag is empty.']);
    exit;
}

// Read input
$rawInput = file_get_contents('php://input');
$json = json_decode($rawInput, true);
$data = is_array($json) ? $json : $_POST;

// Sanitize & validate required fields
$fullName        = trim($data['full_name'] ?? '');
$phone           = trim($data['phone'] ?? '');
$secondaryPhone  = trim($data['secondary_phone'] ?? '');
$email           = trim($data['email'] ?? '');
$addressLine1    = trim($data['address_line1'] ?? '');
$addressLine2    = trim($data['address_line2'] ?? '');
$city            = trim($data['city'] ?? '');
$district        = trim($data['district'] ?? '');
$deliveryNotes   = trim($data['delivery_notes'] ?? '');
$paymentMethod   = 'cod'; // Cash on Delivery

if (empty($fullName) || empty($phone) || empty($addressLine1) || empty($city) || empty($district)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields (Name, Phone, Address, City, District).']);
    exit;
}

// Normalize phone number (+94 / 0 format)
$cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
if (strlen($cleanPhone) < 9) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number.']);
    exit;
}

$pdo = db();

// Calculate financial amounts
$subtotal = 0.0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += (float)$item['price'] * (int)$item['quantity'];
}

// Dynamic Shipping Fee based on District & Free Shipping threshold
$freeShippingThreshold = 7500.00;
$shippingFee = 450.00; // Outstation default

// Check if Western Province / Suburbs
$colomboSuburbs = ['Colombo', 'Gampaha', 'Kalutara'];
if (in_array($district, $colomboSuburbs)) {
    $shippingFee = 350.00;
}

if ($subtotal >= $freeShippingThreshold) {
    $shippingFee = 0.00;
}

// Discount & Coupon calculations
$discountAmount = 0.0;
$couponCodeUsed = null;
$couponId = null;

if (!empty($_SESSION['coupon'])) {
    $c = $_SESSION['coupon'];
    $couponCodeUsed = $c['code'] ?? null;
    $couponId = $c['id'] ?? null;

    if ($c['discount_type'] === 'percentage') {
        $discountAmount = round($subtotal * (((float)$c['discount_value']) / 100), 2);
    } else if ($c['discount_type'] === 'fixed_amount') {
        $discountAmount = min((float)$c['discount_value'], $subtotal);
    } else if ($c['discount_type'] === 'free_shipping') {
        $shippingFee = 0.00;
    }
}

$grandTotal = max(0, $subtotal - $discountAmount + $shippingFee);

try {
    $pdo->beginTransaction();

    // 1. Customer: Check if logged in customer or lookup by phone / create guest
    $loggedInCustId = !empty($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0;

    if ($loggedInCustId > 0) {
        $customerId = $loggedInCustId;
        $upCust = $pdo->prepare("
            UPDATE customers 
            SET total_orders_count = total_orders_count + 1, 
                total_spent_amount = total_spent_amount + ?, 
                last_order_date = NOW() 
            WHERE id = ?
        ");
        $upCust->execute([$grandTotal, $customerId]);
    } else {
        $custStmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ? LIMIT 1");
        $custStmt->execute([$cleanPhone]);
        $existingCustomer = $custStmt->fetch();

        if ($existingCustomer) {
            $customerId = (int)$existingCustomer['id'];
            $upCust = $pdo->prepare("
                UPDATE customers 
                SET full_name = ?, total_orders_count = total_orders_count + 1, total_spent_amount = total_spent_amount + ?, last_order_date = NOW() 
                WHERE id = ?
            ");
            $upCust->execute([$fullName, $grandTotal, $customerId]);
        } else {
            $insCust = $pdo->prepare("
                INSERT INTO customers (phone, secondary_phone, full_name, email, account_type, total_orders_count, total_spent_amount, last_order_date) 
                VALUES (?, ?, ?, ?, 'guest', 1, ?, NOW())
            ");
            $insCust->execute([$cleanPhone, $secondaryPhone ?: null, $fullName, $email ?: null, $grandTotal]);
            $customerId = (int)$pdo->lastInsertId();
        }
    }

    // 2. Generate Unique Order Number (e.g. AME-2026-000101)
    $countOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $orderNumber = 'AME-' . date('Y') . '-' . str_pad((string)($countOrders + 101), 6, '0', STR_PAD_LEFT);

    // Ensure uniqueness
    $checkOrder = $pdo->prepare("SELECT id FROM orders WHERE order_number = ?");
    $checkOrder->execute([$orderNumber]);
    if ($checkOrder->fetch()) {
        $orderNumber = 'AME-' . date('Y') . '-' . str_pad((string)($countOrders + rand(200, 9999)), 6, '0', STR_PAD_LEFT);
    }

    // 3. Insert Master Order
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (
            order_number, customer_id, customer_phone, recipient_name,
            shipping_address_line1, shipping_address_line2, shipping_city, shipping_district,
            delivery_notes, currency_code, exchange_rate_used,
            subtotal_amount, discount_amount, coupon_code_used, shipping_fee, grand_total, 
            payment_method, payment_status, order_status
        ) VALUES (
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, 'LKR', 1.000000,
            ?, ?, ?, ?, ?, 
            'cod', 'pending', 'placed'
        )
    ");
    $orderStmt->execute([
        $orderNumber, $customerId, $cleanPhone, $fullName,
        $addressLine1, $addressLine2 ?: null, $city, $district,
        $deliveryNotes ?: null,
        $subtotal, $discountAmount, $couponCodeUsed, $shippingFee, $grandTotal
    ]);
    $orderId = (int)$pdo->lastInsertId();

    // 4. Update coupon usage if used
    if ($couponId) {
        $updCoupon = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
        $updCoupon->execute([$couponId]);
    }

    // 5. Insert Order Items & Deduct Stock from product_variants
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (
            order_id, product_id, variant_id, product_name_snapshot,
            sku_snapshot, color_snapshot, size_snapshot, quantity, unit_price, line_total
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stockCheckStmt = $pdo->prepare("SELECT total_stock_quantity FROM product_variants WHERE id = ? FOR UPDATE");
    $stockDeductStmt = $pdo->prepare("UPDATE product_variants SET total_stock_quantity = total_stock_quantity - ? WHERE id = ?");

    foreach ($_SESSION['cart'] as $item) {
        $variantId = (int)$item['variant_id'];
        $qty = (int)$item['quantity'];
        $price = (float)$item['price'];
        $lineTotal = $price * $qty;

        // Verify stock lock
        $stockCheckStmt->execute([$variantId]);
        $availStock = (int)$stockCheckStmt->fetchColumn();

        if ($availStock < $qty) {
            throw new Exception("Item '{$item['name']}' (Size: {$item['size']}) does not have enough stock available (Only {$availStock} left).");
        }

        // Insert item record
        $itemStmt->execute([
            $orderId, (int)$item['product_id'], $variantId, $item['name'],
            $item['sku'], $item['color'], $item['size'], $qty, $price, $lineTotal
        ]);

        // CRUCIAL: Deduct stock from product_variants
        $stockDeductStmt->execute([$qty, $variantId]);
    }

    // 6. Insert Timeline Record into order_status_history
    $historyStmt = $pdo->prepare("
        INSERT INTO order_status_history (order_id, status, public_comment) 
        VALUES (?, 'placed', 'Order placed successfully via 60-Second Guest Checkout. Awaiting order verification.')
    ");
    $historyStmt->execute([$orderId]);

    // 7. Generate Invoice Record
    $invoiceNumber = 'INV-' . date('Y') . '-' . substr($orderNumber, 9);
    $invStmt = $pdo->prepare("INSERT INTO invoices (order_id, invoice_number) VALUES (?, ?)");
    $invStmt->execute([$orderId, $invoiceNumber]);

    $pdo->commit();

    // 8. Clear Session Cart and Coupon
    $_SESSION['cart'] = [];
    unset($_SESSION['coupon']);

    echo json_encode([
        'success'      => true,
        'order_number' => $orderNumber,
        'redirect'     => base_url('checkout-success?order=' . $orderNumber)
    ]);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
