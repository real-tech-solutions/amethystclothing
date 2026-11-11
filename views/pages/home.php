<?php
/**
 * Homepage / Phase 1 Visual Verification Page — Amethystclothing.lk
 * Sharp Minimalist Luxury & Streetwear Grid
 */

$pdo = db();

// Fetch 4 seeded featured products with specs and categories
$query = "
    SELECT 
        p.id,
        p.name,
        p.slug,
        p.base_sku_prefix,
        p.base_price,
        p.sale_price,
        p.badge_text,
        p.short_description,
        ps.style_name,
        ps.colour_label,
        ps.material,
        ps.thickness_gsm,
        ps.size_range_label,
        ps.specialities,
        c.name AS category_name,
        c.slug AS category_slug
    FROM products p
    LEFT JOIN product_specifications ps ON p.id = ps.product_id
    LEFT JOIN product_categories pc ON p.id = pc.product_id
    LEFT JOIN categories c ON pc.category_id = c.id
    WHERE p.status = 'active'
    GROUP BY p.id
    ORDER BY p.id ASC
";
$products = $pdo->query($query)->fetchAll();

// Fetch variants for each product
$productVariants = [];
if (!empty($products)) {
    $ids = implode(',', array_column($products, 'id'));
    $varStmt = $pdo->query("SELECT product_id, sku, color_name, color_hex, size_code, total_stock_quantity FROM product_variants WHERE product_id IN ($ids) ORDER BY sort_order ASC");
    while ($v = $varStmt->fetch()) {
        $productVariants[$v['product_id']][] = $v;
    }
}

// Fetch categories for the collection strip
$categories = $pdo->query("SELECT name, slug, description FROM categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();
?>

<!-- 1. HIGH-IMPACT HERO SECTION -->
<section class="hero-section">
    <div class="container hero-section__inner">
        <div class="hero-section__content">
            <div class="hero-badge">
                <span class="hero-badge__tag">DROP // 001</span>
                <span class="hero-badge__text">NEW STREETWEAR DROP LIVE</span>
            </div>
            <h1 class="hero-title">JUST OWN YOUR STYLE!</h1>
            <p class="hero-manifesto">
                High-density combed cotton. Razor-sharp cuts. 180 to 220 GSM heavyweight drop-shoulder streetwear crafted for the uncompromising.
            </p>
            <div class="hero-actions">
                <a href="<?= base_url('shop') ?>" class="btn btn--primary">EXPLORE COLLECTION</a>
                <a href="<?= base_url('category/oversized-comfort') ?>" class="btn btn--outline">220 GSM OVERSIZED</a>
            </div>
            <div class="hero-specs-strip">
                <div class="hero-spec-item">
                    <span class="hero-spec-item__label">FABRIC STANDARD</span>
                    <span class="hero-spec-item__value">100% COMBED COTTON</span>
                </div>
                <div class="hero-spec-item">
                    <span class="hero-spec-item__label">COLLECTION WEIGHT</span>
                    <span class="hero-spec-item__value">180 – 220 GSM</span>
                </div>
                <div class="hero-spec-item">
                    <span class="hero-spec-item__label">EXCHANGE POLICY</span>
                    <span class="hero-spec-item__value">14-DAY EASY EXCHANGE</span>
                </div>
            </div>
        </div>
        <div class="hero-section__visual">
            <div class="hero-art-card">
                <div class="hero-art-card__header">
                    <span class="hero-art-card__badge">LIMITED DROP</span>
                    <span class="barcode-tag">*AMETHYST-001*</span>
                </div>
                <div class="hero-art-card__body">
                    <span class="hero-art-card__watermark">AMETHYST</span>
                    <div class="hero-art-card__quote">
                        <span class="quote-line">ZERO</span>
                        <span class="quote-line quote-line--accent">APOLOGIES</span>
                        <span class="quote-sub">HEAVYWEIGHT 220 GSM STREETWEAR</span>
                    </div>
                </div>
                <div class="hero-art-card__footer">
                    <span>SRI LANKA ORIGINAL</span>
                    <span class="hero-art-card__live-dot">AVAILABLE NOW</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. ARCHITECTURAL VALUE PROPOSITIONS STRIP -->
<section class="value-strip">
    <div class="container value-strip__inner">
        <div class="value-item">
            <div class="value-item__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
            </div>
            <div class="value-item__content">
                <h4 class="value-item__title">ISLANDWIDE DELIVERY</h4>
                <p class="value-item__desc">1–3 Days Colombo & Suburbs. 3–5 Days Outstation.</p>
            </div>
        </div>
        <div class="value-item">
            <div class="value-item__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
            </div>
            <div class="value-item__content">
                <h4 class="value-item__title">14-DAY SIZE EXCHANGE</h4>
                <p class="value-item__desc">No hassles. Seamless doorstep size exchange service.</p>
            </div>
        </div>
        <div class="value-item">
            <div class="value-item__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="1" y="4" width="22" height="16" rx="0" ry="0"></rect>
                    <line x1="1" y1="10" x2="23" y2="10"></line>
                </svg>
            </div>
            <div class="value-item__content">
                <h4 class="value-item__title">CASH ON DELIVERY & KOKO</h4>
                <p class="value-item__desc">Pay Cash at doorstep or split into 3 with Koko / Mintpay.</p>
            </div>
        </div>
        <div class="value-item">
            <div class="value-item__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <div class="value-item__content">
                <h4 class="value-item__title">100% COMBED COTTON</h4>
                <p class="value-item__desc">Anti-shrink, colorfast, export-grade tailored durability.</p>
            </div>
        </div>
    </div>
</section>

<!-- 3. FEATURED DROP GRID (DATABASE-DRIVEN VERIFICATION) -->
<section class="section products-section">
    <div class="container">
        <div class="section-head">
            <div class="section-head__left">
                <span class="section-eyebrow">CURATED SELECTION // SEED VERIFICATION</span>
                <h2 class="section-title">THE INITIAL DROP</h2>
            </div>
            <div class="section-head__right">
                <a href="<?= base_url('shop') ?>" class="section-link">VIEW ENTIRE ARCHIVE &rarr;</a>
            </div>
        </div>

        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <?php 
                    $vars = $productVariants[$p['id']] ?? [];
                    $sizes = array_unique(array_column($vars, 'size_code'));
                    $colors = [];
                    foreach ($vars as $v) {
                        $colors[$v['color_name']] = $v['color_hex'];
                    }
                ?>
                <article class="product-card">
                    <!-- Product Top Bar (SKU + Badge) -->
                    <div class="product-card__meta-bar">
                        <span class="product-card__sku"><?= e($p['base_sku_prefix']) ?></span>
                        <?php if (!empty($p['badge_text'])): ?>
                            <span class="product-badge product-badge--<?= strtolower(str_replace(' ', '-', $p['badge_text'])) ?>">
                                <?= e($p['badge_text']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Visual Box / Garment Mock Graphic -->
                    <div class="product-card__visual">
                        <div class="product-mock-display">
                            <span class="product-mock-display__watermark">AMETHYST</span>
                            <div class="product-mock-display__label">
                                <span class="product-mock-display__style"><?= e($p['style_name'] ?? 'STREETWEAR') ?></span>
                                <span class="product-mock-display__gsm"><?= e($p['thickness_gsm'] ?? '200 GSM') ?></span>
                            </div>
                        </div>
                        <div class="product-card__barcode-strip">
                            <span class="barcode-tag">*<?= e($p['base_sku_prefix']) ?>*</span>
                        </div>
                    </div>

                    <!-- Product Body Content -->
                    <div class="product-card__body">
                        <div class="product-card__cat-row">
                            <span class="product-card__category"><?= e($p['category_name'] ?? 'Streetwear') ?></span>
                            <span class="product-card__fabric"><?= e($p['material'] ?? 'Combed Cotton') ?></span>
                        </div>

                        <h3 class="product-card__title">
                            <a href="<?= base_url('product/' . $p['slug']) ?>"><?= e($p['name']) ?></a>
                        </h3>

                        <p class="product-card__desc"><?= e($p['short_description']) ?></p>

                        <!-- Swatches & Sizes -->
                        <div class="product-card__options">
                            <div class="swatches">
                                <?php foreach ($colors as $cName => $cHex): ?>
                                    <span class="swatch" style="background-color: <?= e($cHex) ?>;" title="<?= e($cName) ?>"></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="size-tags">
                                <?php foreach ($sizes as $sCode): ?>
                                    <span class="size-tag"><?= e($sCode) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Price Row -->
                        <div class="product-card__price-row">
                            <div class="product-card__prices">
                                <?php if (!empty($p['sale_price']) && $p['sale_price'] < $p['base_price']): ?>
                                    <span class="price-current"><?= format_price((float)$p['sale_price']) ?></span>
                                    <span class="price-base"><?= format_price((float)$p['base_price']) ?></span>
                                <?php else: ?>
                                    <span class="price-current"><?= format_price((float)$p['base_price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="<?= base_url('product/' . $p['slug']) ?>" class="btn btn--sm btn--primary">
                                VIEW FIT
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 4. CATEGORIES ARCHITECTURAL SHOWCASE -->
<section class="section categories-section">
    <div class="container">
        <div class="section-head">
            <div class="section-head__left">
                <span class="section-eyebrow">SILHOUETTES & CLASSIFICATIONS</span>
                <h2 class="section-title">EXPLORE BY CATEGORY</h2>
            </div>
        </div>

        <div class="category-grid">
            <?php foreach ($categories as $idx => $cat): ?>
                <a href="<?= base_url('category/' . $cat['slug']) ?>" class="category-card">
                    <div class="category-card__header">
                        <span class="category-card__index">0<?= $idx + 1 ?> //</span>
                        <span class="category-card__sku">CAT-<?= strtoupper(substr($cat['slug'], 0, 4)) ?></span>
                    </div>
                    <div class="category-card__body">
                        <h3 class="category-card__title"><?= e($cat['name']) ?></h3>
                        <p class="category-card__desc"><?= e($cat['description']) ?></p>
                    </div>
                    <div class="category-card__footer">
                        <span>EXPLORE ARCHIVE</span>
                        <span class="category-card__arrow">&rarr;</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 5. STREETWEAR MANIFESTO / AUTHENTICITY BANNER -->
<section class="manifesto-banner">
    <div class="container manifesto-banner__inner">
        <div class="manifesto-banner__tag">
            <span class="barcode-tag">*SRI-LANKA-ORIGINAL*</span>
        </div>
        <h2 class="manifesto-banner__title">WE DON'T DO GENERIC.<br>WE DON'T DO COMPROMISE.</h2>
        <p class="manifesto-banner__text">
            Every thread is spun from combed yarn. Every print is calibrated for zero fade. Built for those who walk into the room and own their style without saying a single word.
        </p>
        <div class="manifesto-banner__cta">
            <a href="<?= base_url('shop') ?>" class="btn btn--primary">SHOP STREETWEAR NOW</a>
        </div>
    </div>
</section>
