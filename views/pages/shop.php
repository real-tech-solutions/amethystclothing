<?php
/**
 * Shop Archive Page — Amethystclothing.lk
 * Phase 6: Advanced Multi-Facet Filter Engine & 2-Column Catalog Layout
 */
$pageTitle = 'Shop All Streetwear & Custom Apparel — Amethystclothing.lk';

$pdo = db();

// Preload filter facets
$q          = trim($_GET['q'] ?? '');
$activeCat  = trim($_GET['category'] ?? '');
$minPrice   = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$maxPrice   = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$inStock    = !empty($_GET['in_stock']) ? 1 : 0;
$sort       = trim($_GET['sort'] ?? 'featured');

$activeSizes = !empty($_GET['sizes']) 
    ? (is_array($_GET['sizes']) ? $_GET['sizes'] : explode(',', $_GET['sizes'])) 
    : [];
$activeSizes = array_filter(array_map('trim', $activeSizes));

// 1. Fetch Categories with Product Counts
$catRows = $pdo->query("
    SELECT c.id, c.name, c.slug, c.parent_id, COUNT(DISTINCT pc.product_id) AS prod_count
    FROM categories c
    LEFT JOIN product_categories pc ON c.id = pc.category_id
    WHERE c.is_active = 1
    GROUP BY c.id
    ORDER BY c.sort_order ASC, c.name ASC
")->fetchAll();

// Total active products count
$totalCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();

// 2. Fetch Distinct Variant Sizes
$availableSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'];

// 3. Initial Server-Side Product Fetch
$where = ["p.status = 'active'"];
$params = [];

if ($q !== '') {
    $where[] = "(p.name LIKE ? OR p.base_sku_prefix LIKE ? OR p.short_description LIKE ? OR p.search_keywords LIKE ?)";
    $like = '%' . $q . '%';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}

if ($activeCat !== '' && $activeCat !== 'all') {
    $where[] = "(c.slug = ? OR pc.category_id IN (SELECT id FROM categories WHERE parent_id = (SELECT id FROM categories WHERE slug = ? LIMIT 1)))";
    $params[] = $activeCat;
    $params[] = $activeCat;
}

if ($minPrice !== null && $minPrice >= 0) {
    $where[] = "COALESCE(NULLIF(p.sale_price, 0), p.base_price) >= ?";
    $params[] = $minPrice;
}
if ($maxPrice !== null && $maxPrice > 0) {
    $where[] = "COALESCE(NULLIF(p.sale_price, 0), p.base_price) <= ?";
    $params[] = $maxPrice;
}

if ($inStock) {
    $where[] = "EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.total_stock_quantity > 0 AND pv.is_active = 1)";
}

if (!empty($activeSizes)) {
    $sizeHolders = implode(',', array_fill(0, count($activeSizes), '?'));
    $where[] = "EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.size_code IN ($sizeHolders) AND pv.is_active = 1)";
    foreach ($activeSizes as $s) $params[] = $s;
}

$orderBy = "p.is_featured DESC, p.id ASC";
switch ($sort) {
    case 'newest':     $orderBy = "p.created_at DESC, p.id DESC"; break;
    case 'price_asc':  $orderBy = "COALESCE(NULLIF(p.sale_price, 0), p.base_price) ASC, p.id ASC"; break;
    case 'price_desc': $orderBy = "COALESCE(NULLIF(p.sale_price, 0), p.base_price) DESC, p.id ASC"; break;
    case 'rating':     $orderBy = "p.average_rating DESC, p.id ASC"; break;
}

// Count total products for pagination
$countSql = "
    SELECT COUNT(DISTINCT p.id)
    FROM products p
    LEFT JOIN product_specifications ps ON p.id = ps.product_id
    LEFT JOIN product_categories pc ON p.id = pc.product_id
    LEFT JOIN categories c ON pc.category_id = c.id
    WHERE " . implode(' AND ', $where) . "
";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$filteredTotal = (int)$countStmt->fetchColumn();

// Pagination variables
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 8;
$totalPages = max(1, (int)ceil($filteredTotal / $perPage));
if ($page > $totalPages && $filteredTotal > 0) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$sql = "
    SELECT 
        p.*, 
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
    LIMIT {$perPage} OFFSET {$offset}
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<section class="section" style="padding-top: 36px;">
    <div class="container">
        <!-- Section Header -->
        <div class="section-head" style="margin-bottom: 24px; border-bottom: 1px solid var(--border-crisp); padding-bottom: 20px;">
            <div class="section-head__left">
                <span class="section-eyebrow"><?= e(__('new_arrivals_tag')) ?> // FULL ARCHIVE</span>
                <h1 class="section-title"><?= e(__('nav_shop_all')) ?></h1>
                <p class="section-subtitle">Heavyweight 220–240 GSM oversized streetwear, precision graphic prints, and essential minimalist silhouettes.</p>
            </div>
            <div class="section-head__right">
                <span class="product-count-badge" id="catalog-count-badge"><?= $filteredTotal ?> ITEMS FOUND</span>
            </div>
        </div>

        <!-- 2-Column Catalog Architecture -->
        <div class="catalog-layout">
            <!-- Left Sticky Filter Sidebar -->
            <aside class="catalog-sidebar" id="catalog-sidebar">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;" class="catalog-sidebar__header">
                    <span style="font-family:var(--font-mono); font-size:12px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase;">
                        FILTERS &amp; REFINEMENTS
                    </span>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <button type="button" id="catalog-clear-all" style="background:none; border:none; cursor:pointer; font-family:var(--font-mono); font-size:10px; color:var(--brand-deep); text-decoration:underline; font-weight:700;">
                            CLEAR ALL
                        </button>
                        <button type="button" id="mobile-filter-close" class="mobile-filter-close" aria-label="Close filters" style="background:none; border:none; cursor:pointer; font-size:22px; line-height:1; color:var(--text-primary); padding:0 4px;">&times;</button>
                    </div>
                </div>

                <!-- 1. Search Query Input -->
                <div class="catalog-filter-group">
                    <label class="catalog-filter-title">
                        <span>KEYWORD SEARCH</span>
                    </label>
                    <div style="position:relative;">
                        <input type="text" id="filter-search-input" value="<?= e($q) ?>" placeholder="Search cuts, GSM, style..." 
                               style="width:100%; padding:8px 10px; font-size:12px; border:1px solid var(--border-crisp); border-radius:0; box-sizing:border-box;">
                    </div>
                </div>

                <!-- 2. Categories Hierarchy -->
                <div class="catalog-filter-group">
                    <div class="catalog-filter-title">
                        <span>CATEGORIES</span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        <button type="button" class="filter-cat-btn <?= ($activeCat === '' || $activeCat === 'all') ? 'is-active' : '' ?>" 
                                data-category="all"
                                style="text-align:left; background:none; border:none; padding:5px 8px; cursor:pointer; font-size:12px; display:flex; justify-content:space-between; align-items:center; transition:background 0.15s; <?= ($activeCat === '' || $activeCat === 'all') ? 'background:var(--brand-deep); color:#FFF; font-weight:700;' : 'color:var(--text-primary);' ?>">
                            <span>ALL SILHOUETTES</span>
                            <span style="font-family:var(--font-mono); font-size:10px; opacity:0.8;"><?= $totalCount ?></span>
                        </button>
                        <?php foreach ($catRows as $cat): ?>
                            <button type="button" class="filter-cat-btn <?= ($activeCat === $cat['slug']) ? 'is-active' : '' ?>" 
                                    data-category="<?= e($cat['slug']) ?>"
                                    style="text-align:left; background:none; border:none; padding:5px 8px; cursor:pointer; font-size:12px; display:flex; justify-content:space-between; align-items:center; transition:background 0.15s; <?= ($activeCat === $cat['slug']) ? 'background:var(--brand-deep); color:#FFF; font-weight:700;' : 'color:var(--text-primary);' ?>">
                                <span><?= !empty($cat['parent_id']) ? '&mdash; ' : '' ?><?= e($cat['name']) ?></span>
                                <span style="font-family:var(--font-mono); font-size:10px; opacity:0.8;"><?= (int)$cat['prod_count'] ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 3. Price Range Tiers -->
                <div class="catalog-filter-group">
                    <div class="catalog-filter-title">
                        <span>PRICE (LKR)</span>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px;">
                        <div>
                            <input type="number" id="filter-min-price" value="<?= $minPrice !== null ? $minPrice : '' ?>" placeholder="Min" step="100" min="0"
                                   style="width:100%; padding:6px 8px; font-size:12px; border:1px solid var(--border-crisp); border-radius:0; box-sizing:border-box;">
                        </div>
                        <div>
                            <input type="number" id="filter-max-price" value="<?= $maxPrice !== null ? $maxPrice : '' ?>" placeholder="Max" step="100" min="0"
                                   style="width:100%; padding:6px 8px; font-size:12px; border:1px solid var(--border-crisp); border-radius:0; box-sizing:border-box;">
                        </div>
                    </div>
                </div>

                <!-- 4. Sizes -->
                <div class="catalog-filter-group">
                    <div class="catalog-filter-title">
                        <span>SIZES</span>
                    </div>
                    <div class="filter-pill-grid" id="filter-sizes-container">
                        <?php foreach ($availableSizes as $sz): 
                            $isSelected = in_array($sz, $activeSizes);
                        ?>
                            <button type="button" class="filter-pill size-filter-btn <?= $isSelected ? 'is-active' : '' ?>" data-size="<?= e($sz) ?>">
                                <?= e($sz) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 5. Availability Checkbox -->
                <div class="catalog-filter-group">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:12px; user-select:none;">
                        <input type="checkbox" id="filter-instock-checkbox" <?= $inStock ? 'checked' : '' ?> style="border-radius:0;">
                        <span>IN-STOCK ITEMS ONLY</span>
                    </label>
                </div>
            </aside>

            <!-- Right Product Grid Area -->
            <main class="catalog-main">
                <!-- Top Control Bar -->
                <div class="catalog-top-bar">
                    <div class="active-filter-tags" id="active-filter-tags">
                        <!-- Populated dynamically via JS -->
                    </div>

                    <div style="display:flex; align-items:center; gap:12px;">
                        <button type="button" id="mobile-filter-toggle" class="btn btn--outline btn--sm" style="display:none; padding:6px 12px; font-size:11px; border-radius:0;">
                            FILTERS ☰
                        </button>

                        <div style="display:flex; align-items:center; gap:6px;">
                            <label for="catalog-sort-select" style="font-family:var(--font-mono); font-size:11px; text-transform:uppercase; color:#716B80;">SORT:</label>
                            <select id="catalog-sort-select" style="padding:6px 10px; font-size:12px; font-family:var(--font-mono); border:1px solid var(--border-crisp); border-radius:0; background:#FFF;">
                                <option value="featured" <?= $sort === 'featured' ? 'selected' : '' ?>>RECOMMENDED</option>
                                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>NEWEST DROPS</option>
                                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>PRICE: LOW &rarr; HIGH</option>
                                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>PRICE: HIGH &rarr; LOW</option>
                                <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>TOP CUSTOMER RATED</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Product Grid Container -->
                <div class="product-grid" id="catalog-product-grid">
                    <?php if (empty($products)): ?>
                        <div style="grid-column: 1 / -1; padding: 60px 20px; text-align: center; background: #FFFFFF; border: 1px solid var(--border-crisp);">
                            <div style="font-family: var(--font-mono); font-size: 11px; letter-spacing: 0.15em; color: var(--brand-deep); text-transform: uppercase; margin-bottom: 6px;">FILTER ENGINE // 0 RESULTS</div>
                            <h3 style="font-family: var(--font-display); font-size: 20px; margin: 0 0 8px; text-transform: uppercase;">NO MATCHING SILHOUETTES</h3>
                            <p style="font-size: 13px; color: var(--text-secondary); margin: 0 0 20px;">Try adjusting your search keywords, price range, or clearing active filters.</p>
                            <button type="button" id="btn-reset-filters-inner" class="btn btn--outline btn--sm" style="border-radius:0;">RESET ALL FILTERS</button>
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
                    <?php endif; ?>
                </div>

                <!-- Server-Side Pagination Bar -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination-bar" id="catalog-pagination-bar" style="margin-top: 36px; display:flex; justify-content:center; align-items:center; gap:8px;">
                        <?php if ($page > 1): 
                            $prevParams = $_GET; $prevParams['page'] = $page - 1;
                        ?>
                            <a href="?<?= http_build_query($prevParams) ?>" class="btn btn--outline btn--sm" style="border-radius:0; padding:6px 14px; font-family:var(--font-mono);">&larr; PREV</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): 
                            $pageParams = $_GET; $pageParams['page'] = $i;
                            $isCur = ($i === $page);
                        ?>
                            <a href="?<?= http_build_query($pageParams) ?>" 
                               style="display:inline-block; padding:6px 12px; font-family:var(--font-mono); font-size:12px; font-weight:700; text-decoration:none; border:1px solid var(--border-crisp); <?= $isCur ? 'background:var(--brand-deep); color:#FFF; border-color:var(--brand-deep);' : 'background:#FFF; color:var(--text-primary);' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): 
                            $nextParams = $_GET; $nextParams['page'] = $page + 1;
                        ?>
                            <a href="?<?= http_build_query($nextParams) ?>" class="btn btn--outline btn--sm" style="border-radius:0; padding:6px 14px; font-family:var(--font-mono);">NEXT &rarr;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</section>
