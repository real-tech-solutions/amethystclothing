<?php
/**
 * Amethystclothing.lk — Live Predictive Search Endpoint
 * GET /ajax/live_search.php?q=keyword
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../core/helpers.php';

$query = trim($_GET['q'] ?? '');

if (mb_strlen($query) < 2) {
    echo json_encode([
        'success'    => true,
        'query'      => $query,
        'count'      => 0,
        'products'   => [],
        'categories' => [],
        'view_all_url' => base_url('shop?q=' . urlencode($query))
    ]);
    exit;
}

try {
    $pdo = db();
    $like = '%' . $query . '%';

    // 1. Matched Categories
    $catStmt = $pdo->prepare("
        SELECT id, name, slug 
        FROM categories 
        WHERE is_active = 1 AND (name LIKE ? OR slug LIKE ?)
        ORDER BY sort_order ASC, name ASC 
        LIMIT 4
    ");
    $catStmt->execute([$like, $like]);
    $matchedCats = $catStmt->fetchAll();

    $categories = [];
    foreach ($matchedCats as $c) {
        $categories[] = [
            'id'   => (int)$c['id'],
            'name' => $c['name'],
            'slug' => $c['slug'],
            'url'  => base_url('category/' . $c['slug'])
        ];
    }

    // 2. Matched Products
    $prodStmt = $pdo->prepare("
        SELECT 
            p.id, 
            p.name, 
            p.slug, 
            p.base_sku_prefix, 
            p.base_price, 
            p.sale_price, 
            ps.thickness_gsm, 
            c.name AS category_name,
            (SELECT url FROM product_media WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image
        FROM products p
        LEFT JOIN product_specifications ps ON p.id = ps.product_id
        LEFT JOIN product_categories pc ON p.id = pc.product_id
        LEFT JOIN categories c ON pc.category_id = c.id
        WHERE p.status = 'active' 
          AND (
              p.name LIKE ? 
              OR p.base_sku_prefix LIKE ? 
              OR p.short_description LIKE ? 
              OR p.search_keywords LIKE ?
              OR c.name LIKE ?
          )
        GROUP BY p.id
        ORDER BY p.id ASC
        LIMIT 6
    ");
    $prodStmt->execute([$like, $like, $like, $like, $like]);
    $matchedProds = $prodStmt->fetchAll();

    $products = [];
    foreach ($matchedProds as $p) {
        $price = !empty($p['sale_price']) && $p['sale_price'] < $p['base_price']
            ? (float)$p['sale_price']
            : (float)$p['base_price'];

        $products[] = [
            'id'              => (int)$p['id'],
            'name'            => $p['name'],
            'slug'            => $p['slug'],
            'sku'             => $p['base_sku_prefix'],
            'category_name'   => $p['category_name'] ?? 'Apparel',
            'thickness_gsm'   => $p['thickness_gsm'] ?? '220 GSM',
            'formatted_price' => format_price($price),
            'image'           => !empty($p['primary_image']) 
                ? $p['primary_image'] 
                : 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=800&h=1000&q=80',
            'url'             => base_url('product/' . $p['slug'])
        ];
    }

    echo json_encode([
        'success'      => true,
        'query'        => $query,
        'count'        => count($products),
        'products'     => $products,
        'categories'   => $categories,
        'view_all_url' => base_url('shop?q=' . urlencode($query))
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Search query failed: ' . $e->getMessage()
    ]);
}
