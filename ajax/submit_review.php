<?php
/**
 * Amethystclothing.lk — Customer Review Submission Endpoint
 * POST /ajax/submit_review.php
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

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$reviewerName = trim($_POST['reviewer_name'] ?? '');
$reviewerPhone = trim($_POST['reviewer_phone'] ?? '');
$reviewTitle = trim($_POST['review_title'] ?? '');
$reviewText = trim($_POST['review_text'] ?? '');
$purchasedSize = trim($_POST['purchased_size'] ?? 'M');

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Please provide a star rating between 1 and 5.']);
    exit;
}

if (empty($reviewerName) || empty($reviewTitle) || empty($reviewText)) {
    echo json_encode(['success' => false, 'message' => 'Please complete all required fields (Name, Review Title, and Review Details).']);
    exit;
}

try {
    // Verify product exists
    $pStmt = db()->prepare("SELECT id FROM products WHERE id = ? AND status = 'active' LIMIT 1");
    $pStmt->execute([$productId]);
    if (!$pStmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit;
    }

    // Customer Resolution (Logged in vs Phone vs Guest)
    $customerId = 0;
    if (!empty($_SESSION['customer_id'])) {
        $customerId = (int)$_SESSION['customer_id'];
    } else if (!empty($reviewerPhone)) {
        $cleanPhone = preg_replace('/[^0-9+]/', '', $reviewerPhone);
        $cStmt = db()->prepare("SELECT id FROM customers WHERE phone = ? LIMIT 1");
        $cStmt->execute([$cleanPhone]);
        $existing = $cStmt->fetch();
        if ($existing) {
            $customerId = (int)$existing['id'];
        } else {
            $insCust = db()->prepare("INSERT INTO customers (full_name, phone, account_type, created_at) VALUES (?, ?, 'guest', NOW())");
            $insCust->execute([$reviewerName, $cleanPhone]);
            $customerId = (int)db()->lastInsertId();
        }
    } else {
        // Find existing guest or create anonymous guest record
        $cStmt = db()->prepare("SELECT id FROM customers WHERE full_name = ? LIMIT 1");
        $cStmt->execute([$reviewerName]);
        $existing = $cStmt->fetch();
        if ($existing) {
            $customerId = (int)$existing['id'];
        } else {
            $tempPhone = 'GUEST-' . time() . '-' . rand(100, 999);
            $insCust = db()->prepare("INSERT INTO customers (full_name, phone, account_type, created_at) VALUES (?, ?, 'guest', NOW())");
            $insCust->execute([$reviewerName, $tempPhone]);
            $customerId = (int)db()->lastInsertId();
        }
    }

    // Check if verified buyer
    $isVerified = 0;
    if ($customerId > 0) {
        $vStmt = db()->prepare("
            SELECT COUNT(*) 
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            WHERE o.customer_id = ? 
              AND oi.product_id = ? 
              AND o.order_status NOT IN ('cancelled', 'returned')
        ");
        $vStmt->execute([$customerId, $productId]);
        if ((int)$vStmt->fetchColumn() > 0) {
            $isVerified = 1;
        }
    }

    // Insert Review with status = 'pending'
    $insRev = db()->prepare("
        INSERT INTO product_reviews 
        (product_id, customer_id, rating, review_title, review_text, purchased_size, is_verified_buyer, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $insRev->execute([
        $productId,
        $customerId,
        $rating,
        $reviewTitle,
        $reviewText,
        $purchasedSize,
        $isVerified
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your review has been submitted for moderation and will appear once approved by our curation team.'
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
