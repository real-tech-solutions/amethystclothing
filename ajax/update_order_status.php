<?php
/**
 * Amethystclothing.lk — Order Status Update Endpoint
 * POST /ajax/update_order_status.php
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/helpers.php';

if (!is_admin_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$orderNumber = trim($_POST['order_number'] ?? '');
$newStatus = trim($_POST['order_status'] ?? '');
$courierPartner = trim($_POST['courier_partner'] ?? '');
$courierTrackingNumber = trim($_POST['courier_tracking_number'] ?? '');
$publicComment = trim($_POST['public_comment'] ?? '');
$paymentStatus = trim($_POST['payment_status'] ?? '');

$allowedStatuses = [
    'placed',
    'confirmed',
    'printing_processing',
    'packed',
    'handed_to_courier',
    'out_for_delivery',
    'delivered',
    'cancelled',
    'returned'
];

if (!in_array($newStatus, $allowedStatuses, true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid order status selected.']);
    exit;
}

$admin = admin_user();
$adminId = $admin['id'] ?? null;

try {
    // 1. Fetch current order
    if ($orderId > 0) {
        $stmt = db()->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        $stmt->execute([$orderId]);
    } else if (!empty($orderNumber)) {
        $stmt = db()->prepare("SELECT * FROM orders WHERE order_number = ? LIMIT 1");
        $stmt->execute([$orderNumber]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Order identifier missing.']);
        exit;
    }

    $order = $stmt->fetch();
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }

    $orderId = (int)$order['id'];
    $oldStatus = $order['order_status'];

    // If payment status not provided, keep current or auto-mark paid on delivered COD
    if (empty($paymentStatus)) {
        $paymentStatus = $order['payment_status'];
        if ($newStatus === 'delivered' && $order['payment_method'] === 'cod' && $paymentStatus === 'unpaid') {
            $paymentStatus = 'paid';
        }
    }

    // Default courier values if not passed
    $partnerToSet = $courierPartner ?: $order['courier_partner'];
    $trackingToSet = $courierTrackingNumber ?: $order['courier_tracking_number'];

    // Default comment if empty
    if (empty($publicComment)) {
        $statusLabels = [
            'placed' => 'Order placed by customer.',
            'confirmed' => 'Order confirmed and approved for production.',
            'printing_processing' => 'Custom graphic screen printing and stitching in progress.',
            'packed' => 'Order quality inspected and sealed in Amethyst luxury packaging.',
            'handed_to_courier' => 'Package handed over to dispatch courier partner.',
            'out_for_delivery' => 'Package is out for delivery with local courier agent.',
            'delivered' => 'Package successfully delivered and received.',
            'cancelled' => 'Order has been cancelled.',
            'returned' => 'Order returned by customer/courier.'
        ];
        $publicComment = $statusLabels[$newStatus] ?? 'Order status updated.';
    }

    db()->beginTransaction();

    // 2. Update orders table
    $upd = db()->prepare("
        UPDATE orders 
        SET order_status = :status,
            payment_status = :payment_status,
            courier_partner = :courier_partner,
            courier_tracking_number = :courier_tracking,
            updated_at = NOW()
        WHERE id = :id
    ");
    $upd->execute([
        'status' => $newStatus,
        'payment_status' => $paymentStatus,
        'courier_partner' => $partnerToSet,
        'courier_tracking' => $trackingToSet,
        'id' => $orderId
    ]);

    // 3. Insert into order_status_history
    $hist = db()->prepare("
        INSERT INTO order_status_history (order_id, status, public_comment, updated_by_admin_id, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $hist->execute([$orderId, $newStatus, $publicComment, $adminId]);

    db()->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Order #' . $order['order_number'] . ' updated to ' . strtoupper(str_replace('_', ' ', $newStatus)),
        'order_status' => $newStatus,
        'payment_status' => $paymentStatus,
        'status_formatted' => strtoupper(str_replace('_', ' ', $newStatus))
    ]);
    exit;

} catch (Exception $e) {
    if (db()->inTransaction()) {
        db()->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
