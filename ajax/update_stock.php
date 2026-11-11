<?php
/**
 * Amethystclothing.lk — Inventory Quick Stock Update Endpoint
 * POST /ajax/update_stock.php
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

$variantId = isset($_POST['variant_id']) ? (int)$_POST['variant_id'] : 0;
$stockQuantity = isset($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : -1;

if ($variantId <= 0 || $stockQuantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid variant ID or stock quantity (must be >= 0).']);
    exit;
}

try {
    // 1. Check if variant exists
    $stmt = db()->prepare("
        SELECT pv.id, pv.sku, pv.color_name, pv.size_code, p.name as product_name 
        FROM product_variants pv
        JOIN products p ON p.id = pv.product_id
        WHERE pv.id = ? 
        LIMIT 1
    ");
    $stmt->execute([$variantId]);
    $variant = $stmt->fetch();

    if (!$variant) {
        echo json_encode(['success' => false, 'message' => 'Product variant not found.']);
        exit;
    }

    // 2. Update stock
    $upd = db()->prepare("UPDATE product_variants SET total_stock_quantity = ? WHERE id = ?");
    $upd->execute([$stockQuantity, $variantId]);

    // 3. Compute status badge data
    $badgeClass = 'badge-instock';
    $statusText = 'IN STOCK';
    if ($stockQuantity === 0) {
        $badgeClass = 'badge-outofstock';
        $statusText = 'OUT OF STOCK';
    } else if ($stockQuantity <= 5) {
        $badgeClass = 'badge-lowstock';
        $statusText = 'LOW STOCK (' . $stockQuantity . ')';
    }

    echo json_encode([
        'success' => true,
        'variant_id' => $variantId,
        'sku' => $variant['sku'],
        'new_quantity' => $stockQuantity,
        'status_text' => $statusText,
        'badge_class' => $badgeClass,
        'message' => 'Stock for ' . $variant['sku'] . ' updated to ' . $stockQuantity . ' units.'
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
