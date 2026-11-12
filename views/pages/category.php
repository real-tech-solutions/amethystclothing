<?php
/**
 * Category Archive Page — Amethystclothing.lk
 * Phase 6: Subcategory Navigation, Multi-Facet Filter Engine & 2-Column Catalog Layout
 */
$slug = $slug ?? '';
$pdo = db();

$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$category = $stmt->fetch();

$pageTitle = $category ? e($category['name']) . ' — Amethystclothing.lk' : 'Category — Amethystclothing.lk';

if (!$category) {
    ?>
    <section class="section" style="text-align:center; padding: 100px 20px;">
        <div class="container">
            <h1 class="section-title">CATEGORY NOT FOUND</h1>
            <p style="color:var(--text-secondary); margin: 12px 0 24px;">The collection you are looking for does not exist or has been archived.</p>
            <a href="<?= base_url('shop') ?>" class="btn btn--primary"><?= e(__('continue_shopping')) ?></a>
        </div>
    </section>
    <?php
    return;
}

// 1. Fetch Subcategories (if any) or Sister Subcategories (if this is a child)
$subcategories = [];
if (empty($category['parent_id'])) {
    // This is a parent category -> fetch direct children
    $subStmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order ASC, name ASC");
    $subStmt->execute([$category['id']]);
    $subcategories = $subStmt->fetchAll();
} else {
    // This is a subcategory -> fetch siblings
    $subStmt = $pdo->prepare("SELECT * FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order ASC, name ASC");
    $subStmt->execute([$category['parent_id']]);
    $subcategories = $subStmt->fetchAll();
}

// 2. Fetch distinct sizes & colors
$availableSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'];

$colorRows = $pdo->query("
    SELECT DISTINCT color_name, color_hex
    FROM product_variants
    WHERE is_active = 1 AND color_name IS NOT NULL AND color_hex IS NOT NULL
    GROUP BY color_name, color_hex
    ORDER BY color_name ASC
")->fetchAll();

// 3. Preload initial products for this category (including its children if parent)
$pStmt = $pdo->prepare("
    SELECT 
        p.*, 
        ps.style_name, 
        ps.thickness_gsm, 
        ps.material,
        c.name AS category_name,
        c.slug AS category_slug,
        (SELECT url FROM product_media WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image
    FROM products p
    JOIN product_categories pc ON p.id = pc.product_id
    JOIN categories c ON pc.category_id = c.id
    LEFT JOIN product_specifications ps ON p.id = ps.product_id
    WHERE (c.id = ? OR c.parent_id = ?) AND p.status = 'active'
    GROUP BY p.id
    ORDER BY p.is_featured DESC, p.id ASC
");
$pStmt->execute([$category['id'], $category['id']]);
$products = $pStmt->fetchAll();

$catBanner = !empty($category['banner_url']) 
    ? $category['banner_url'] 
    : 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=1600&q=80';
?>

<!-- Minimalist High-Impact Category Hero -->
<section class="category-hero" style="background-image: linear-gradient(180deg, rgba(11,9,16,0.65) 0%, rgba(11,9,16,0.94) 100%), url('<?= e($catBanner) ?>'); padding: 60px 0;">
    <div class="container category-hero__inner">
        <span class="category-hero__eyebrow"><?= e(__('category_sec_tag')) ?> // ARCHIVE</span>
        <h1 class="category-hero__title" style="font-size: 38px;"><?= e($category['name']) ?></h1>
        <p class="category-hero__desc" style="max-width: 600px;"><?= e($category['description']) ?></p>

        <?php if (!empty($subcategories)): ?>
            <!-- Subcategory Quick Nav Pills -->
            <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:20px;">
                <a href="<?= base_url('category/' . $category['slug']) ?>" 
                   style="font-family:var(--font-mono); font-size:11px; padding:6px 14px; background:#FFFFFF; color:#0B0910; font-weight:700; text-transform:uppercase; text-decoration:none;">
                    ALL <?= e(strtoupper($category['name'])) ?>
                </a>
                <?php foreach ($subcategories as $sub): ?>
                    <a href="<?= base_url('category/' . $sub['slug']) ?>" 
                       style="font-family:var(--font-mono); font-size:11px; padding:6px 14px; background:rgba(255,255,255,0.15); color:#FFFFFF; border:1px solid rgba(255,255,255,0.3); text-transform:uppercase; text-decoration:none; transition:all 0.15s;">
                        <?= e($sub['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section" style="padding-top: 36px;">
    <div class="container">
        <!-- 2-Column Catalog Architecture -->
        <div class="catalog-layout">
            <!-- Left Sticky Filter Sidebar -->
            <aside class="catalog-sidebar" id="catalog-sidebar">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;" class="catalog-sidebar__header">
                    <span style="font-family:var(--font-mono); font-size:12px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase;">
                        FILTERS & REFINEMENTS
                    </span>
                    <button type="button" id="catalog-clear-all" style="background:none; border:none; cursor:pointer; font-family:var(--font-mono); font-size:10px; color:var(--brand-deep); text-decoration:underline; font-weight:700;">
                        CLEAR ALL
                    </button>
                </div>

                <!-- Hidden locked category input -->
                <input type="hidden" id="filter-locked-category" value="<?= e($category['slug']) ?>">

                <!-- 1. Search Query Input -->
                <div class="catalog-filter-group">
                    <label class="catalog-filter-title">
                        <span>SEARCH IN THIS CATEGORY</span>
                    </label>
                    <div>
                        <input type="text" id="filter-search-input" value="" placeholder="Search silhouettes..." 
                               style="width:100%; padding:8px 10px; font-size:12px; border:1px solid var(--border-crisp); border-radius:0; box-sizing:border-box;">
                    </div>
                </div>

                <?php if (!empty($subcategories)): ?>
                    <!-- Subcategories Filter Group -->
                    <div class="catalog-filter-group">
                        <div class="catalog-filter-title">
                            <span>SUB-SILHOUETTES</span>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:4px;">
                            <?php foreach ($subcategories as $sub): ?>
                                <a href="<?= base_url('category/' . $sub['slug']) ?>" 
                                   style="text-align:left; background:none; border:none; padding:5px 8px; font-size:12px; text-decoration:none; color:var(--text-primary); display:flex; justify-content:space-between; align-items:center;">
                                    <span><?= e($sub['name']) ?></span>
                                    <span style="font-family:var(--font-mono); font-size:10px;">&rarr;</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 2. Price Range Tiers -->
                <div class="catalog-filter-group">
                    <div class="catalog-filter-title">
                        <span>PRICE (LKR)</span>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; margin-bottom:8px;">
                        <div>
                            <input type="number" id="filter-min-price" placeholder="Min" step="100" min="0"
                                   style="width:100%; padding:6px 8px; font-size:12px; border:1px solid var(--border-crisp); border-radius:0; box-sizing:border-box;">
                        </div>
                        <div>
                            <input type="number" id="filter-max-price" placeholder="Max" step="100" min="0"
                                   style="width:100%; padding:6px 8px; font-size:12px; border:1px solid var(--border-crisp); border-radius:0; box-sizing:border-box;">
                        </div>
                    </div>
                    <div class="filter-pill-grid">
                        <button type="button" class="filter-pill price-tier-btn" data-min="" data-max="2500">&lt; 2,500</button>
                        <button type="button" class="filter-pill price-tier-btn" data-min="2500" data-max="3500">2,500 - 3,500</button>
                        <button type="button" class="filter-pill price-tier-btn" data-min="3500" data-max="">&gt; 3,500</button>
                    </div>
                </div>

                <!-- 3. Sizes -->
                <div class="catalog-filter-group">
                    <div class="catalog-filter-title">
                        <span>SIZES</span>
                    </div>
                    <div class="filter-pill-grid" id="filter-sizes-container">
                        <?php foreach ($availableSizes as $sz): ?>
                            <button type="button" class="filter-pill size-filter-btn" data-size="<?= e($sz) ?>">
                                <?= e($sz) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 4. Colors -->
                <?php if (!empty($colorRows)): ?>
                    <div class="catalog-filter-group">
                        <div class="catalog-filter-title">
                            <span>COLORS</span>
                        </div>
                        <div class="filter-swatch-grid" id="filter-colors-container">
                            <?php foreach ($colorRows as $col): ?>
                                <button type="button" class="filter-swatch color-filter-btn" 
                                        data-color-name="<?= e($col['color_name']) ?>"
                                        data-color-hex="<?= e($col['color_hex']) ?>"
                                        title="<?= e($col['color_name']) ?>"
                                        style="background-color: <?= e($col['color_hex']) ?>;"></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 5. Availability Checkbox -->
                <div class="catalog-filter-group">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:12px; user-select:none;">
                        <input type="checkbox" id="filter-instock-checkbox" style="border-radius:0;">
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
                                <option value="featured" selected>RECOMMENDED</option>
                                <option value="newest">NEWEST DROPS</option>
                                <option value="price_asc">PRICE: LOW &rarr; HIGH</option>
                                <option value="price_desc">PRICE: HIGH &rarr; LOW</option>
                                <option value="rating">TOP CUSTOMER RATED</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Product Grid Container -->
                <div class="product-grid" id="catalog-product-grid">
                    <?php if (empty($products)): ?>
                        <div style="grid-column: 1 / -1; padding: 60px 20px; text-align: center; background: #FFFFFF; border: 1px solid var(--border-crisp);">
                            <div style="font-family: var(--font-mono); font-size: 11px; letter-spacing: 0.15em; color: var(--brand-deep); text-transform: uppercase; margin-bottom: 6px;">ARCHIVE // 0 RESULTS</div>
                            <h3 style="font-family: var(--font-display); font-size: 20px; margin: 0 0 8px; text-transform: uppercase;">NO SILHOUETTES IN THIS COLLECTION</h3>
                            <p style="font-size: 13px; color: var(--text-secondary); margin: 0 0 20px;">Explore all streetwear and custom apparel across our complete line.</p>
                            <a href="<?= base_url('shop') ?>" class="btn btn--primary btn--sm" style="border-radius:0;"><?= e(__('continue_shopping')) ?></a>
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
                                    <span class="product-card__category"><?= e($category['name']) ?></span>
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
            </main>
        </div>
    </div>
</section>
