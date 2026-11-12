<?php
/**
 * Amethystclothing.lk — Admin Size Chart Management Endpoint
 * POST /ajax/admin_size_chart.php
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

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$action = trim($_POST['action'] ?? '');

try {
    // -------------------------------------------------------------
    // ACTION: GET (Fetch details for edit or preview)
    // -------------------------------------------------------------
    if ($action === 'get') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare("SELECT * FROM size_charts WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $chart = $stmt->fetch();

        if (!$chart) {
            echo json_encode(['success' => false, 'message' => 'Size chart not found.']);
            exit;
        }

        $chart['measurements'] = json_decode($chart['measurements_json'], true) ?: [];
        echo json_encode(['success' => true, 'chart' => $chart]);
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: SAVE (Create or Update)
    // -------------------------------------------------------------
    if ($action === 'save') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = trim($_POST['title'] ?? '');
        $fitType = trim($_POST['fit_type'] ?? 'Regular');
        $notes = trim($_POST['notes'] ?? '');
        $measurementsRaw = $_POST['measurements_json'] ?? '';

        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Size guide title is required.']);
            exit;
        }

        // Validate or parse measurements
        $measurements = [];
        if (is_array($measurementsRaw)) {
            $measurements = $measurementsRaw;
        } else if (is_string($measurementsRaw)) {
            $measurements = json_decode($measurementsRaw, true);
        }

        if (empty($measurements) || !is_array($measurements)) {
            echo json_encode(['success' => false, 'message' => 'Please provide valid size measurement specifications.']);
            exit;
        }

        // Sanitize measurement rows
        $cleanMeasurements = [];
        foreach ($measurements as $m) {
            if (empty($m['size'])) continue;
            $cleanMeasurements[] = [
                'size' => strtoupper(trim($m['size'])),
                'chest_inch' => (float)($m['chest_inch'] ?? 0),
                'length_inch' => (float)($m['length_inch'] ?? 0),
                'shoulder_inch' => (float)($m['shoulder_inch'] ?? 0)
            ];
        }

        $jsonStr = json_encode($cleanMeasurements);

        if ($id > 0) {
            $stmt = db()->prepare("
                UPDATE size_charts 
                SET title = ?, fit_type = ?, notes = ?, measurements_json = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $fitType, $notes, $jsonStr, $id]);
            $msg = "Size guide '{$title}' updated successfully.";
        } else {
            $stmt = db()->prepare("
                INSERT INTO size_charts (title, fit_type, notes, measurements_json, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$title, $fitType, $notes, $jsonStr]);
            $id = (int)db()->lastInsertId();
            $msg = "Size guide '{$title}' created successfully.";
        }

        echo json_encode([
            'success' => true,
            'message' => $msg,
            'id' => $id
        ]);
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: DELETE
    // -------------------------------------------------------------
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            exit;
        }

        db()->beginTransaction();

        // Unlink products using this size chart
        $upd = db()->prepare("UPDATE products SET size_chart_id = NULL WHERE size_chart_id = ?");
        $upd->execute([$id]);

        $del = db()->prepare("DELETE FROM size_charts WHERE id = ?");
        $del->execute([$id]);

        db()->commit();

        echo json_encode(['success' => true, 'message' => 'Size chart deleted and unlinked from products.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;

} catch (Exception $e) {
    if (db()->inTransaction()) db()->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
