<?php
/**
 * Shop Archive Page — Amethystclothing.lk
 */
$pageTitle = 'Shop All Streetwear & Custom Apparel — Amethystclothing.lk';

$pdo = db();
$products = $pdo->query("
    SELECT 
        p.*, 
        ps.style_name, 
        ps.thickness_gsm, 
        ps.material, 
        c.name AS category_name,
        (SELECT url FROM product_media WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image
    FROM products p
    LEFT JOIN product_specifications ps ON p.id = ps.product_id
    LEFT JOIN product_categories pc ON p.id = pc.product_id
    LEFT JOIN categories c ON pc.category_id = c.id
    WHERE p.status = 'active'
    GROUP BY p.id
    ORDER BY p.id ASC
")->fetchAll();
?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <div class="section-head__left">
                <span class="section-eyebrow"><?= e(__('new_arrivals_tag')) ?></span>
                <h1 class="section-title"><?= e(__('nav_shop_all')) ?></h1>
                <p class="section-subtitle">Premium custom t-shirt printing, 220 GSM heavyweight oversized streetwear, and timeless essential cuts.</p>
            </div>
            <div class="section-head__right">
                <span class="product-count-badge"><?= count($products) ?> ITEMS FOUND</span>
            </div>
        </div>

        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <?php 
                    $imgUrl = !empty($p['primary_image']) ? $p['primary_image'] : 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=800&h=1000&q=80';
                ?>
                <article class="product-card">
                    <div class="product-card__meta-bar">
                        <span class="product-card__sku"><?= e($p['base_sku_prefix']) ?></span>
                        <?php if (!empty($p['badge_text'])): ?>
                            <span class="product-badge"><?= e($p['badge_text']) ?></span>
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
                                <span class="price-current"><?= format_price((float)($p['sale_price'] ?? $p['base_price'])) ?></span>
                            </div>
                            <a href="<?= base_url('product/' . $p['slug']) ?>" class="btn btn--sm btn--primary"><?= e(__('view_details')) ?></a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
