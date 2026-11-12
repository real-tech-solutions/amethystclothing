<?php
/**
 * Amethystclothing.lk — Advanced Multi-Facet Filter Engine Endpoint
 * GET or POST /ajax/filter_products.php
 */

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

require_once __DIR__ . '/../core/helpers.php';

try {
    $pdo = db();

    // 1. Collect inputs from GET or POST
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $input = $method === 'POST' && !empty($_POST) ? $_POST : array_merge($_GET, $_POST);

    $q          = trim($input['q'] ?? '');
    $category   = trim($input['category'] ?? '');
    $minPrice   = isset($input['min_price']) && $input['min_price'] !== '' ? (float)$input['min_price'] : null;
    $maxPrice   = isset($input['max_price']) && $input['max_price'] !== '' ? (float)$input['max_price'] : null;
    $inStock    = !empty($input['in_stock']) ? 1 : 0;
    $sort       = trim($input['sort'] ?? 'featured');

    // Parse sizes
    $sizes = [];
    if (!empty($input['sizes'])) {
        if (is_array($input['sizes'])) {
            $sizes = array_filter(array_map('trim', $input['sizes']));
        } else if (is_string($input['sizes'])) {
            $sizes = array_filter(array_map('trim', explode(',', $input['sizes'])));
        }
    }

    // 2. Build Query
    $where = ["p.status = 'active'"];
    $params = [];

    // Search query
    if ($q !== '') {
        $where[] = "(p.name LIKE ? OR p.base_sku_prefix LIKE ? OR p.short_description LIKE ? OR p.search_keywords LIKE ? OR c.name LIKE ?)";
        $like = '%' . $q . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    // Category filter
    if ($category !== '' && $category !== 'all') {
        if (ctype_digit($category)) {
            $catId = (int)$category;
            $where[] = "(pc.category_id = ? OR pc.category_id IN (SELECT id FROM categories WHERE parent_id = ?))";
            $params[] = $catId;
            $params[] = $catId;
        } else {
            $where[] = "(c.slug = ? OR pc.category_id IN (SELECT id FROM categories WHERE parent_id = (SELECT id FROM categories WHERE slug = ? LIMIT 1)))";
            $params[] = $category;
            $params[] = $category;
        }
    }

    // Price range
    if ($minPrice !== null && $minPrice >= 0) {
        $where[] = "COALESCE(NULLIF(p.sale_price, 0), p.base_price) >= ?";
        $params[] = $minPrice;
    }
    if ($maxPrice !== null && $maxPrice > 0) {
        $where[] = "COALESCE(NULLIF(p.sale_price, 0), p.base_price) <= ?";
        $params[] = $maxPrice;
    }

    // In Stock Only
    if ($inStock) {
        $where[] = "EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.total_stock_quantity > 0 AND pv.is_active = 1)";
    }

    // Size Filter
    if (!empty($sizes)) {
        $sizeHolders = implode(',', array_fill(0, count($sizes), '?'));
        $where[] = "EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.size_code IN ($sizeHolders) AND pv.is_active = 1)";
        foreach ($sizes as $s) {
            $params[] = $s;
        }
    }

    // Order By
    $orderBy = "p.id ASC";
    switch ($sort) {
        case 'newest':
            $orderBy = "p.created_at DESC, p.id DESC";
            break;
        case 'price_asc':
            $orderBy = "COALESCE(NULLIF(p.sale_price, 0), p.base_price) ASC, p.id ASC";
            break;
        case 'price_desc':
            $orderBy = "COALESCE(NULLIF(p.sale_price, 0), p.base_price) DESC, p.id ASC";
            break;
        case 'rating':
            $orderBy = "p.average_rating DESC, p.id ASC";
            break;
        case 'featured':
        default:
            $orderBy = "p.is_featured DESC, p.id ASC";
            break;
    }

    $sql = "
        SELECT 
            p.id,
            p.name,
            p.slug,
            p.base_sku_prefix,
            p.short_description,
            p.base_price,
            p.sale_price,
            p.badge_text,
            p.average_rating,
            ps.style_name,
            ps.thickness_gsm,
            ps.material,
            c.name AS category_name,
            c.slug AS category_slug,
            (SELECT url FROM product_media WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image
        FROM products p
        LEFT JOIN product_specifications ps ON p.id = ps.product_id
        LEFT JOIN product_categories pc ON p.id = pc.product_id
        LEFT JOIN categories c ON pc.category_id = c.id
        WHERE " . implode(' AND ', $where) . "
        GROUP BY p.id
        ORDER BY {$orderBy}
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // 3. Render HTML
    ob_start();
    if (empty($products)): ?>
        <div style="grid-column: 1 / -1; padding: 60px 20px; text-align: center; background: #FFFFFF; border: 1px solid var(--border-crisp);">
            <div style="font-family: var(--font-mono); font-size: 11px; letter-spacing: 0.15em; color: var(--brand-deep); text-transform: uppercase; margin-bottom: 6px;">FILTER ENGINE // 0 RESULTS</div>
            <h3 style="font-family: var(--font-display); font-size: 20px; margin: 0 0 8px; text-transform: uppercase;">NO MATCHING SILHOUETTES</h3>
            <p style="font-size: 13px; color: var(--text-secondary); margin: 0 0 20px;">Try adjusting your search keywords, price range, or clearing active filters.</p>
            <button type="button" id="btn-reset-filters" class="btn btn--outline btn--sm" style="border-radius:0;">RESET ALL FILTERS</button>
        </div>
    <?php else: ?>
        <?php foreach ($products as $p): 
            $imgUrl = !empty($p['primary_image']) 
                ? $p['primary_image'] 
                : 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=800&h=1000&q=80';
            $hasSale = !empty($p['sale_price']) && $p['sale_price'] < $p['base_price'];
        ?>
            <article class="product-card">
                <div class="product-card__meta-bar">
                    <span class="product-card__sku"><?= e($p['base_sku_prefix']) ?></span>
                    <?php if (!empty($p['badge_text'])): ?>
                        <span class="product-badge product-badge--<?= strtolower(str_replace(' ', '-', $p['badge_text'])) ?>"><?= e($p['badge_text']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="product-card__visual">
                    <a href="<?= base_url('product/' . $p['slug']) ?>" class="product-card__img-link">
                        <img src="<?= e($imgUrl) ?>" alt="<?= e($p['name']) ?>" class="product-card__img" loading="lazy">
                    </a>
                    <div class="product-card__tech-tag">
                        <span><?= e($p['thickness_gsm'] ?? '200 GSM') ?></span>
                    </div>
                </div>
                <div class="product-card__body">
                    <span class="product-card__category"><?= e($p['category_name'] ?? 'Streetwear') ?></span>
                    <h3 class="product-card__title">
                        <a href="<?= base_url('product/' . $p['slug']) ?>"><?= e($p['name']) ?></a>
                    </h3>
                    <p class="product-card__desc"><?= e($p['short_description']) ?></p>
                    <div class="product-card__price-row">
                        <div class="product-card__prices">
                            <?php if ($hasSale): ?>
                                <span class="price-current"><?= format_price((float)$p['sale_price']) ?></span>
                                <span class="price-base" style="font-size:12px; text-decoration:line-through; color:#8C869B;"><?= format_price((float)$p['base_price']) ?></span>
                            <?php else: ?>
                                <span class="price-current"><?= format_price((float)$p['base_price']) ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="<?= base_url('product/' . $p['slug']) ?>" class="btn btn--sm btn--primary"><?= e(__('view_details')) ?></a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif;
    $html = ob_get_clean();

    // 4. Return JSON
    echo json_encode([
        'success'        => true,
        'count'          => count($products),
        'active_filters' => [
            'q'         => $q,
            'category'  => $category,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sizes'     => $sizes,
            'in_stock'  => $inStock,
            'sort'      => $sort
        ],
        'products'       => $products,
        'html'           => $html
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Filter failed: ' . $e->getMessage()
    ]);
}
