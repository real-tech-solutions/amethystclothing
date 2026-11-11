<?php
/**
 * Amethystclothing.lk — Discount Coupon Validation Endpoint
 * POST /ajax/apply_coupon.php
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$action = trim($_POST['action'] ?? 'apply');

// 1. Handle Coupon Removal
if ($action === 'remove') {
    unset($_SESSION['coupon']);
    echo json_encode([
        'success' => true,
        'message' => 'Promo code removed.',
        'discount_amount' => 0,
        'discount_formatted' => format_price(0)
    ]);
    exit;
}

// 2. Validate Cart & Subtotal
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Your bag is empty. Add items before applying promo code.']);
    exit;
}

$subtotal = 0.0;
foreach ($cart as $item) {
    $subtotal += ((float)$item['price']) * ((int)$item['quantity']);
}

$code = strtoupper(trim($_POST['coupon_code'] ?? ''));

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid promo code.']);
    exit;
}

try {
    $stmt = db()->prepare("
        SELECT * FROM coupons 
        WHERE BINARY code = ? 
          AND is_active = 1 
          AND valid_from <= NOW() 
          AND valid_until >= NOW() 
        LIMIT 1
    ");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired promo code.']);
        exit;
    }

    // Check usage limits
    if ($coupon['usage_limit_total'] !== null && (int)$coupon['usage_limit_total'] > 0) {
        if ((int)$coupon['used_count'] >= (int)$coupon['usage_limit_total']) {
            echo json_encode(['success' => false, 'message' => 'This promo code has reached its maximum global usage limit.']);
            exit;
        }
    }

    // Check minimum order value
    if ($coupon['min_order_value'] !== null && $subtotal < (float)$coupon['min_order_value']) {
        $minValFormatted = format_price((float)$coupon['min_order_value']);
        echo json_encode([
            'success' => false, 
            'message' => "Order subtotal must be at least {$minValFormatted} to activate promo code '{$code}'."
        ]);
        exit;
    }

    // Calculate discount amount
    $discountAmount = 0.0;
    if ($coupon['discount_type'] === 'percentage') {
        $discountAmount = round($subtotal * (((float)$coupon['discount_value']) / 100), 2);
        if ($coupon['max_discount_amount'] !== null && (float)$coupon['max_discount_amount'] > 0) {
            $discountAmount = min($discountAmount, (float)$coupon['max_discount_amount']);
        }
    } else if ($coupon['discount_type'] === 'fixed_amount') {
        $discountAmount = min((float)$coupon['discount_value'], $subtotal);
    } else if ($coupon['discount_type'] === 'free_shipping') {
        $discountAmount = 0.0; // Free shipping handled on shipping line
    }

    // Save to session
    $_SESSION['coupon'] = [
        'id' => (int)$coupon['id'],
        'code' => $coupon['code'],
        'description' => $coupon['description'],
        'discount_type' => $coupon['discount_type'],
        'discount_value' => (float)$coupon['discount_value'],
        'discount_amount' => $discountAmount,
    ];

    echo json_encode([
        'success' => true,
        'message' => "Promo code '{$coupon['code']}' applied! You saved " . format_price($discountAmount),
        'code' => $coupon['code'],
        'discount_amount' => $discountAmount,
        'discount_formatted' => format_price($discountAmount),
        'subtotal' => $subtotal,
        'subtotal_formatted' => format_price($subtotal)
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error verifying coupon: ' . $e->getMessage()]);
    exit;
}
