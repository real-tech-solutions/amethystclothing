<?php
/**
 * Amethystclothing.lk — Admin Review Moderation Endpoint
 * POST /ajax/moderate_review.php
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/helpers.php';

if (!is_admin_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$reviewId = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
$action = trim($_POST['action'] ?? '');

if ($reviewId <= 0 || !in_array($action, ['approve', 'reject', 'delete'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

try {
    $stmt = db()->prepare("SELECT id, product_id, review_title FROM product_reviews WHERE id = ? LIMIT 1");
    $stmt->execute([$reviewId]);
    $rev = $stmt->fetch();

    if (!$rev) {
        echo json_encode(['success' => false, 'message' => 'Review not found.']);
        exit;
    }

    $productId = (int)$rev['product_id'];

    if ($action === 'delete') {
        $del = db()->prepare("DELETE FROM product_reviews WHERE id = ?");
        $del->execute([$reviewId]);
        $msg = "Review deleted.";
    } else {
        $newStatus = ($action === 'approve') ? 'approved' : 'rejected';
        $upd = db()->prepare("UPDATE product_reviews SET status = ? WHERE id = ?");
        $upd->execute([$newStatus, $reviewId]);
        $msg = "Review " . ucfirst($newStatus) . " successfully.";
    }

    // Recalculate product aggregate ratings
    $stats = db()->prepare("
        SELECT COUNT(*) as total_reviews, COALESCE(AVG(rating), 5.0) as avg_rating
        FROM product_reviews 
        WHERE product_id = ? AND status = 'approved'
    ");
    $stats->execute([$productId]);
    $res = $stats->fetch();

    $avg = round((float)$res['avg_rating'], 2);
    $count = (int)$res['total_reviews'];

    $updProd = db()->prepare("UPDATE products SET average_rating = ?, reviews_count = ? WHERE id = ?");
    $updProd->execute([$avg, $count, $productId]);

    echo json_encode([
        'success' => true,
        'message' => $msg,
        'review_id' => $reviewId,
        'new_status' => $action === 'delete' ? 'deleted' : $newStatus,
        'average_rating' => $avg,
        'reviews_count' => $count
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
