<?php
/**
 * Homepage — Amethystclothing.lk
 * Phase 2: Sleek Hero Slider, Scroll-Snap Product Carousel, Image-backed Categories, Clean Bilingual Typography
 */

$pdo = db();

// Fetch products with their primary and secondary images from product_media
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
        c.slug AS category_slug,
        (SELECT url FROM product_media WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image,
        (SELECT url FROM product_media WHERE product_id = p.id AND is_primary = 0 ORDER BY sort_order ASC LIMIT 1) AS secondary_image
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

// Fetch categories with banner URLs
$categories = $pdo->query("SELECT name, slug, description, banner_url, icon_or_image_url FROM categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();
?>

<!-- 1. SLEEK AUTOPLAYING HERO SLIDER (VANILLA JS & CSS) -->
<section class="hero-slider" id="hero-slider" aria-label="Featured Highlights">
    <div class="hero-slider__track" id="hero-slider-track">
        <!-- Slide 1 -->
        <div class="hero-slide is-active" style="background-image: linear-gradient(90deg, rgba(11, 9, 16, 0.88) 0%, rgba(11, 9, 16, 0.65) 55%, rgba(11, 9, 16, 0.25) 100%), url('https://images.unsplash.com/photo-1558769132-cb1aea458c5e?auto=format&fit=crop&w=1920&h=1080&q=85');">
            <div class="container hero-slide__inner">
                <div class="hero-slide__content">
                    <div class="hero-badge">
                        <span class="hero-badge__tag">DROP // 001</span>
                        <span class="hero-badge__text"><?= e(__('hero_slide1_badge')) ?></span>
                    </div>
                    <h1 class="hero-title"><?= e(__('hero_slide1_title')) ?></h1>
                    <p class="hero-manifesto"><?= e(__('hero_slide1_sub')) ?></p>
                    <div class="hero-actions">
                        <a href="<?= base_url('shop') ?>" class="btn btn--primary"><?= e(__('explore_collection')) ?></a>
                        <a href="<?= base_url('category/oversized-comfort') ?>" class="btn btn--outline-light"><?= e(__('view_oversized')) ?></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 2 -->
        <div class="hero-slide" style="background-image: linear-gradient(90deg, rgba(11, 9, 16, 0.88) 0%, rgba(11, 9, 16, 0.6) 55%, rgba(11, 9, 16, 0.25) 100%), url('https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?auto=format&fit=crop&w=1920&h=1080&q=85');">
            <div class="container hero-slide__inner">
                <div class="hero-slide__content">
                    <div class="hero-badge">
                        <span class="hero-badge__tag">HEAVYWEIGHT</span>
                        <span class="hero-badge__text"><?= e(__('hero_slide2_badge')) ?></span>
                    </div>
                    <h2 class="hero-title"><?= e(__('hero_slide2_title')) ?></h2>
                    <p class="hero-manifesto"><?= e(__('hero_slide2_sub')) ?></p>
                    <div class="hero-actions">
                        <a href="<?= base_url('category/oversized-comfort') ?>" class="btn btn--primary"><?= e(__('view_oversized')) ?></a>
                        <a href="<?= base_url('shop') ?>" class="btn btn--outline-light"><?= e(__('explore_collection')) ?></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 3 -->
        <div class="hero-slide" style="background-image: linear-gradient(90deg, rgba(11, 9, 16, 0.88) 0%, rgba(11, 9, 16, 0.6) 55%, rgba(11, 9, 16, 0.25) 100%), url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1920&h=1080&q=85');">
            <div class="container hero-slide__inner">
                <div class="hero-slide__content">
                    <div class="hero-badge">
                        <span class="hero-badge__tag">CUSTOM PRINTS</span>
                        <span class="hero-badge__text"><?= e(__('hero_slide3_badge')) ?></span>
                    </div>
                    <h2 class="hero-title"><?= e(__('hero_slide3_title')) ?></h2>
                    <p class="hero-manifesto"><?= e(__('hero_slide3_sub')) ?></p>
                    <div class="hero-actions">
                        <a href="<?= base_url('category/couple-collections') ?>" class="btn btn--primary"><?= e(__('nav_couple')) ?></a>
                        <a href="https://wa.me/94770000000?text=<?= urlencode('Hi Amethyst! I want custom t-shirt printing.') ?>" target="_blank" class="btn btn--outline-light"><?= e(__('custom_printing')) ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Slider Navigation Dots & Arrows -->
    <div class="hero-slider__controls">
        <button class="hero-slider__arrow hero-slider__arrow--prev" id="hero-slider-prev" aria-label="Previous Slide">&larr;</button>
        <div class="hero-slider__dots" id="hero-slider-dots">
            <button class="hero-dot is-active" data-slide="0" aria-label="Slide 1"></button>
            <button class="hero-dot" data-slide="1" aria-label="Slide 2"></button>
            <button class="hero-dot" data-slide="2" aria-label="Slide 3"></button>
        </div>
        <button class="hero-slider__arrow hero-slider__arrow--next" id="hero-slider-next" aria-label="Next Slide">&rarr;</button>
    </div>
</section>

<!-- 2. VALUE PROPOSITIONS STRIP -->
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
                <h4 class="value-item__title"><?= e(__('delivery_title')) ?></h4>
                <p class="value-item__desc"><?= e(__('delivery_desc')) ?></p>
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
                <h4 class="value-item__title"><?= e(__('exchange_title')) ?></h4>
                <p class="value-item__desc"><?= e(__('exchange_desc')) ?></p>
            </div>
        </div>
        <div class="value-item">
            <div class="value-item__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="1" y="4" width="22" height="16"></rect>
                    <line x1="1" y1="10" x2="23" y2="10"></line>
                </svg>
            </div>
            <div class="value-item__content">
                <h4 class="value-item__title"><?= e(__('cod_title')) ?></h4>
                <p class="value-item__desc"><?= e(__('cod_desc')) ?></p>
            </div>
        </div>
        <div class="value-item">
            <div class="value-item__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <div class="value-item__content">
                <h4 class="value-item__title"><?= e(__('cotton_title')) ?></h4>
                <p class="value-item__desc"><?= e(__('cotton_desc')) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- 3. FEATURED PRODUCTS (CSS SCROLL-SNAP SLIDER WITH CONTROLS) -->
<section class="section products-section">
    <div class="container">
        <div class="section-head">
            <div class="section-head__left">
                <span class="section-eyebrow"><?= e(__('new_arrivals_tag')) ?></span>
                <h2 class="section-title"><?= e(__('new_arrivals_title')) ?></h2>
            </div>
            <div class="section-head__right">
                <div class="carousel-nav-arrows">
                    <button type="button" class="carousel-btn" id="product-scroll-prev" aria-label="Scroll left">&larr;</button>
                    <button type="button" class="carousel-btn" id="product-scroll-next" aria-label="Scroll right">&rarr;</button>
                </div>
                <a href="<?= base_url('shop') ?>" class="section-link"><?= e(__('view_all_arrow')) ?></a>
            </div>
        </div>

        <!-- Scroll-Snap Horizontal Product Carousel -->
        <div class="product-carousel-wrapper">
            <div class="product-carousel" id="product-carousel">
                <?php foreach ($products as $p): ?>
                    <?php 
                        $vars = $productVariants[$p['id']] ?? [];
                        $sizes = array_unique(array_column($vars, 'size_code'));
                        $colors = [];
                        foreach ($vars as $v) {
                            $colors[$v['color_name']] = $v['color_hex'];
                        }
                        $imgUrl = !empty($p['primary_image']) ? $p['primary_image'] : 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=800&h=1000&q=80';
                        $imgAlt = !empty($p['secondary_image']) ? $p['secondary_image'] : $imgUrl;
                    ?>
                    <article class="product-card">
                        <!-- Top Meta Bar -->
                        <div class="product-card__meta-bar">
                            <span class="product-card__sku"><?= e($p['base_sku_prefix']) ?></span>
                            <?php if (!empty($p['badge_text'])): ?>
                                <span class="product-badge product-badge--<?= strtolower(str_replace(' ', '-', $p['badge_text'])) ?>">
                                    <?= e($p['badge_text']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Real Apparel Image Visual Box -->
                        <div class="product-card__visual">
                            <a href="<?= base_url('product/' . $p['slug']) ?>" class="product-card__img-link">
                                <img src="<?= e($imgUrl) ?>" alt="<?= e($p['name']) ?>" class="product-card__img product-card__img--primary" loading="lazy">
                                <img src="<?= e($imgAlt) ?>" alt="<?= e($p['name']) ?>" class="product-card__img product-card__img--hover" loading="lazy">
                            </a>
                            <div class="product-card__tech-tag">
                                <span><?= e($p['thickness_gsm'] ?? '200 GSM') ?></span>
                            </div>
                        </div>

                        <!-- Product Content -->
                        <div class="product-card__body">
                            <div class="product-card__cat-row">
                                <span class="product-card__category"><?= e($p['category_name'] ?? 'Streetwear') ?></span>
                                <span class="product-card__fabric"><?= e($p['material'] ?? 'Combed Cotton') ?></span>
                            </div>

                            <h3 class="product-card__title">
                                <a href="<?= base_url('product/' . $p['slug']) ?>"><?= e($p['name']) ?></a>
                            </h3>

                            <p class="product-card__desc"><?= e($p['short_description']) ?></p>

                            <!-- Swatches & Available Sizes -->
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

                            <!-- Pricing Row -->
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
                                    <?= e(__('view_details')) ?>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- 4. SHOP BY CATEGORY (WITH HIGH-RES UNSPLASH BACKGROUNDS & GRADIENTS) -->
<section class="section categories-section">
    <div class="container">
        <div class="section-head">
            <div class="section-head__left">
                <span class="section-eyebrow"><?= e(__('category_sec_tag')) ?></span>
                <h2 class="section-title"><?= e(__('category_sec_title')) ?></h2>
            </div>
        </div>

        <div class="category-grid">
            <?php foreach ($categories as $idx => $cat): ?>
                <?php 
                    $catBg = !empty($cat['banner_url']) ? $cat['banner_url'] : 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=800&q=80';
                ?>
                <a href="<?= base_url('category/' . $cat['slug']) ?>" 
                   class="category-card category-card--with-bg" 
                   style="background-image: linear-gradient(180deg, rgba(11, 9, 16, 0.45) 0%, rgba(11, 9, 16, 0.9) 100%), url('<?= e($catBg) ?>');">
                    <div class="category-card__header">
                        <span class="category-card__index">0<?= $idx + 1 ?> //</span>
                        <span class="category-card__sku">COLLECTION</span>
                    </div>
                    <div class="category-card__body">
                        <h3 class="category-card__title"><?= e($cat['name']) ?></h3>
                        <p class="category-card__desc"><?= e($cat['description']) ?></p>
                    </div>
                    <div class="category-card__footer">
                        <span><?= e(__('explore_category')) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 5. BRAND SPECIALTY & CUSTOM PRINTING BANNER -->
<section class="manifesto-banner">
    <div class="container manifesto-banner__inner">
        <div class="manifesto-banner__tag">
            <span class="manifesto-pill">PREMIUM T-SHIRT PRINTING & CUSTOM MERCHANDISE</span>
        </div>
        <h2 class="manifesto-banner__title">WE SPECIALIZE IN BESPOKE PRINTING<br>& EXCLUSIVE APPAREL.</h2>
        <p class="manifesto-banner__text">
            From high-density oversized drop-shoulder tees to corporate and custom band merchandise. We use premium combed cotton, high-precision screen printing, and DTF technology with zero fade.
        </p>
        <div class="manifesto-banner__cta">
            <a href="<?= base_url('shop') ?>" class="btn btn--primary"><?= e(__('explore_collection')) ?></a>
            <a href="https://wa.me/94770000000?text=<?= urlencode('Hi Amethyst! I want to discuss a custom t-shirt printing order.') ?>" target="_blank" class="btn btn--outline-light">
                WHATSAPP PRINTING HOTLINE
            </a>
        </div>
    </div>
</section>
