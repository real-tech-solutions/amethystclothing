<?php
/**
 * Amethystclothing.lk — Customer Address Management Endpoint
 * POST /ajax/save_address.php
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/helpers.php';

$customer = customer_user();
if (!$customer) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in to save an address.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$customerId = (int)$customer['id'];
$recipientName = trim($_POST['recipient_name'] ?? '');
$recipientPhone = trim($_POST['recipient_phone'] ?? '');
$addressLine1 = trim($_POST['address_line1'] ?? '');
$addressLine2 = trim($_POST['address_line2'] ?? '');
$city = trim($_POST['city'] ?? '');
$district = trim($_POST['district'] ?? '');
$postalCode = trim($_POST['postal_code'] ?? '');
$addressLabel = trim($_POST['address_label'] ?? 'Home');
$isDefault = !empty($_POST['is_default']) ? 1 : 0;

if (empty($recipientName) || empty($recipientPhone) || empty($addressLine1) || empty($city) || empty($district)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required address fields.']);
    exit;
}

try {
    db()->beginTransaction();

    if ($isDefault) {
        $clear = db()->prepare("UPDATE customer_addresses SET is_default = 0 WHERE customer_id = ?");
        $clear->execute([$customerId]);
    }

    $ins = db()->prepare("
        INSERT INTO customer_addresses 
        (customer_id, address_label, recipient_name, recipient_phone, address_line1, address_line2, city, district, postal_code, country_code, is_default)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'LK', ?)
    ");
    $ins->execute([
        $customerId,
        $addressLabel,
        $recipientName,
        $recipientPhone,
        $addressLine1,
        $addressLine2 ?: null,
        $city,
        $district,
        $postalCode ?: null,
        $isDefault
    ]);

    db()->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Delivery address saved successfully.'
    ]);
    exit;

} catch (Exception $e) {
    if (db()->inTransaction()) db()->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
