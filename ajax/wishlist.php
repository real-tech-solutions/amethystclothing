<?php
/**
 * Amethystclothing.lk — Wishlist Management Endpoint
 * POST /ajax/wishlist.php
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/helpers.php';

if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

$action = trim($_POST['action'] ?? 'toggle');
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$customerId = !empty($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0;

// Just return count
if ($action === 'count') {
    echo json_encode([
        'success' => true,
        'count' => count($_SESSION['wishlist'])
    ]);
    exit;
}

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

try {
    // Verify product exists
    $stmt = db()->prepare("SELECT id, name FROM products WHERE id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found or unavailable.']);
        exit;
    }

    $inWishlist = in_array($productId, $_SESSION['wishlist']);

    if ($action === 'remove' || ($action === 'toggle' && $inWishlist)) {
        // Remove from session
        $_SESSION['wishlist'] = array_values(array_diff($_SESSION['wishlist'], [$productId]));
        $nowInWishlist = false;
        $msg = "Removed from your Wishlist.";

        // Sync with database if logged in
        if ($customerId > 0) {
            $del = db()->prepare("DELETE FROM wishlists WHERE customer_id = ? AND product_id = ?");
            $del->execute([$customerId, $productId]);
        }
    } else {
        // Add to session
        if (!in_array($productId, $_SESSION['wishlist'])) {
            $_SESSION['wishlist'][] = $productId;
        }
        $nowInWishlist = true;
        $msg = "Saved to your Wishlist.";

        // Sync with database if logged in
        if ($customerId > 0) {
            $check = db()->prepare("SELECT id FROM wishlists WHERE customer_id = ? AND product_id = ?");
            $check->execute([$customerId, $productId]);
            if (!$check->fetch()) {
                $ins = db()->prepare("INSERT INTO wishlists (customer_id, product_id, created_at) VALUES (?, ?, NOW())");
                $ins->execute([$customerId, $productId]);
            }
        }
    }

    echo json_encode([
        'success' => true,
        'in_wishlist' => $nowInWishlist,
        'count' => count($_SESSION['wishlist']),
        'product_id' => $productId,
        'message' => $msg
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating wishlist: ' . $e->getMessage()]);
    exit;
}
