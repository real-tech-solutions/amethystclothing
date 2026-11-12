<?php
/**
 * Amethystclothing.lk — Admin Category Management Endpoint
 * POST /ajax/admin_category.php
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
    // ACTION: SAVE (Create or Update)
    // -------------------------------------------------------------
    if ($action === 'save') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $description = trim($_POST['description'] ?? '');
        $bannerUrl = trim($_POST['banner_url'] ?? '');
        $iconUrl = trim($_POST['icon_or_image_url'] ?? '');
        $sortOrder = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
        $isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Category name is required.']);
            exit;
        }

        if (empty($slug)) {
            $slug = slugify($name);
        } else {
            $slug = slugify($slug);
        }

        // Prevent setting category as its own parent
        if ($id > 0 && $parentId === $id) {
            echo json_encode(['success' => false, 'message' => 'A category cannot be its own parent.']);
            exit;
        }

        // Slug uniqueness check
        if ($id > 0) {
            $chk = db()->prepare("SELECT id FROM categories WHERE slug = ? AND id != ? LIMIT 1");
            $chk->execute([$slug, $id]);
        } else {
            $chk = db()->prepare("SELECT id FROM categories WHERE slug = ? LIMIT 1");
            $chk->execute([$slug]);
        }

        if ($chk->fetch()) {
            // Suffix with random if duplicate
            $slug .= '-' . rand(10, 99);
        }

        if ($id > 0) {
            $stmt = db()->prepare("
                UPDATE categories 
                SET parent_id = ?, name = ?, slug = ?, description = ?, 
                    banner_url = ?, icon_or_image_url = ?, sort_order = ?, 
                    is_featured = ?, is_active = ?, meta_title = ?, meta_description = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $parentId, $name, $slug, $description,
                $bannerUrl, $iconUrl, $sortOrder,
                $isFeatured, $isActive, $metaTitle, $metaDescription,
                $id
            ]);
            $msg = "Category '{$name}' updated successfully.";
        } else {
            $stmt = db()->prepare("
                INSERT INTO categories 
                (parent_id, name, slug, description, banner_url, icon_or_image_url, sort_order, is_featured, is_active, meta_title, meta_description, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $parentId, $name, $slug, $description,
                $bannerUrl, $iconUrl, $sortOrder,
                $isFeatured, $isActive, $metaTitle, $metaDescription
            ]);
            $id = (int)db()->lastInsertId();
            $msg = "Category '{$name}' created successfully.";
        }

        echo json_encode([
            'success' => true,
            'message' => $msg,
            'category_id' => $id,
            'slug' => $slug
        ]);
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: DELETE
    // -------------------------------------------------------------
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid category ID.']);
            exit;
        }

        // Check if has subcategories
        $chkSub = db()->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = ?");
        $chkSub->execute([$id]);
        if ((int)$chkSub->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete: this category has subcategories. Reassign or delete child categories first.']);
            exit;
        }

        db()->beginTransaction();

        // Remove mapping in product_categories
        $delPc = db()->prepare("DELETE FROM product_categories WHERE category_id = ?");
        $delPc->execute([$id]);

        // Delete category
        $del = db()->prepare("DELETE FROM categories WHERE id = ?");
        $del->execute([$id]);

        db()->commit();

        echo json_encode(['success' => true, 'message' => 'Category deleted successfully.']);
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: TOGGLE FEATURED (Homepage display)
    // -------------------------------------------------------------
    if ($action === 'toggle_featured') {
        $id = (int)($_POST['id'] ?? 0);
        $upd = db()->prepare("UPDATE categories SET is_featured = 1 - is_featured WHERE id = ?");
        $upd->execute([$id]);

        $newVal = (int)db()->query("SELECT is_featured FROM categories WHERE id = {$id}")->fetchColumn();

        echo json_encode([
            'success' => true,
            'is_featured' => $newVal,
            'message' => $newVal ? 'Category now displayed on homepage.' : 'Category removed from homepage.'
        ]);
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: TOGGLE ACTIVE
    // -------------------------------------------------------------
    if ($action === 'toggle_active') {
        $id = (int)($_POST['id'] ?? 0);
        $upd = db()->prepare("UPDATE categories SET is_active = 1 - is_active WHERE id = ?");
        $upd->execute([$id]);

        $newVal = (int)db()->query("SELECT is_active FROM categories WHERE id = {$id}")->fetchColumn();

        echo json_encode([
            'success' => true,
            'is_active' => $newVal,
            'message' => $newVal ? 'Category activated.' : 'Category deactivated.'
        ]);
        exit;
    }

    // -------------------------------------------------------------
    // ACTION: UPDATE SORT ORDER
    // -------------------------------------------------------------
    if ($action === 'update_sort_order') {
        $id = (int)($_POST['id'] ?? 0);
        $order = (int)($_POST['sort_order'] ?? 0);

        $upd = db()->prepare("UPDATE categories SET sort_order = ? WHERE id = ?");
        $upd->execute([$order, $id]);

        echo json_encode(['success' => true, 'message' => 'Sort order updated.']);
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
