<?php
/**
 * Amethystclothing.lk — Customer Authentication Endpoint
 * POST /ajax/customer_auth.php
 * Phone-First Authentication (Login & Registration)
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

$action = trim($_POST['action'] ?? 'login');
$redirectTarget = trim($_POST['redirect'] ?? '');

function normalize_lk_phone(string $phone): string {
    $clean = preg_replace('/[^0-9+]/', '', $phone);
    if (str_starts_with($clean, '+94')) {
        $clean = '0' . substr($clean, 3);
    }
    return $clean;
}

try {
    // -------------------------------------------------------------
    // ACTION: REGISTER
    // -------------------------------------------------------------
    if ($action === 'register') {
        $fullName = trim($_POST['full_name'] ?? '');
        $phoneInput = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        if (empty($fullName) || empty($phoneInput) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields (Name, Phone, Password).']);
            exit;
        }

        $phone = normalize_lk_phone($phoneInput);
        if (strlen($phone) < 9) {
            echo json_encode(['success' => false, 'message' => 'Please provide a valid Sri Lankan phone number (e.g. 077XXXXXXX).']);
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
            exit;
        }

        if ($password !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
            exit;
        }

        // Check if customer exists by phone
        $stmt = db()->prepare("SELECT * FROM customers WHERE phone = ? LIMIT 1");
        $stmt->execute([$phone]);
        $existing = $stmt->fetch();

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $customerId = 0;

        if ($existing) {
            // If already has password, account is taken
            if (!empty($existing['password_hash'])) {
                echo json_encode(['success' => false, 'message' => 'An account with this mobile number already exists. Please sign in instead.']);
                exit;
            }

            // Upgrade guest customer to registered
            $upd = db()->prepare("
                UPDATE customers 
                SET full_name = ?, email = COALESCE(NULLIF(?, ''), email), password_hash = ?, account_type = 'registered', updated_at = NOW()
                WHERE id = ?
            ");
            $upd->execute([$fullName, $email ?: null, $hash, $existing['id']]);
            $customerId = (int)$existing['id'];
        } else {
            // Insert brand new customer with 100 welcome loyalty points
            $ins = db()->prepare("
                INSERT INTO customers (phone, full_name, email, password_hash, account_type, points_balance, loyalty_tier, created_at)
                VALUES (?, ?, ?, ?, 'registered', 100, 'Standard', NOW())
            ");
            $ins->execute([$phone, $fullName, $email ?: null, $hash]);
            $customerId = (int)db()->lastInsertId();
        }

        // Log in
        $_SESSION['customer_id'] = $customerId;

        // Sync session wishlist to DB
        if (!empty($_SESSION['wishlist'])) {
            foreach ($_SESSION['wishlist'] as $wPid) {
                $check = db()->prepare("SELECT id FROM wishlists WHERE customer_id = ? AND product_id = ?");
                $check->execute([$customerId, $wPid]);
                if (!$check->fetch()) {
                    $insW = db()->prepare("INSERT INTO wishlists (customer_id, product_id, created_at) VALUES (?, ?, NOW())");
                    $insW->execute([$customerId, $wPid]);
                }
            }
        }

        $dest = !empty($redirectTarget) ? base_url($redirectTarget) : base_url('account/dashboard');
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully. Welcome to Amethyst!',
            'redirect' => $dest
        ]);
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: LOGIN
    // -------------------------------------------------------------
    if ($action === 'login') {
        $loginId = trim($_POST['login_id'] ?? ''); // Phone or Email
        $password = (string)($_POST['password'] ?? '');

        if (empty($loginId) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please enter your phone number and password.']);
            exit;
        }

        $cleanPhone = normalize_lk_phone($loginId);

        $stmt = db()->prepare("SELECT * FROM customers WHERE (phone = ? OR email = ?) LIMIT 1");
        $stmt->execute([$cleanPhone, $loginId]);
        $customer = $stmt->fetch();

        if (!$customer) {
            echo json_encode(['success' => false, 'message' => 'No account found with this phone number or email.']);
            exit;
        }

        if (empty($customer['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'This phone number was used for a guest checkout. Please click "Create Account" above to set a password.']);
            exit;
        }

        if (!password_verify($password, $customer['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Incorrect password. Please verify and try again.']);
            exit;
        }

        // Success: Establish session
        $_SESSION['customer_id'] = (int)$customer['id'];

        // Sync wishlist items from DB to session
        $wStmt = db()->prepare("SELECT product_id FROM wishlists WHERE customer_id = ?");
        $wStmt->execute([$customer['id']]);
        $dbWishlist = $wStmt->fetchAll(PDO::FETCH_COLUMN);

        if (!isset($_SESSION['wishlist'])) $_SESSION['wishlist'] = [];
        $_SESSION['wishlist'] = array_values(array_unique(array_merge($_SESSION['wishlist'], $dbWishlist)));

        $dest = !empty($redirectTarget) ? base_url($redirectTarget) : base_url('account/dashboard');
        echo json_encode([
            'success' => true,
            'message' => 'Welcome back, ' . ($customer['full_name'] ?: 'Customer') . '!',
            'redirect' => $dest
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Authentication error: ' . $e->getMessage()]);
    exit;
}
