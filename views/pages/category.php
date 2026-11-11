<?php
/**
 * Category Page — Amethystclothing.lk
 * Displays category hero banner with Unsplash image and product cards
 */
$slug = $slug ?? '';
$pdo = db();
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$category = $stmt->fetch();

$pageTitle = $category ? e($category['name']) . ' — Amethystclothing.lk' : 'Category — Amethystclothing.lk';

$products = [];
if ($category) {
    $pStmt = $pdo->prepare("
        SELECT 
            p.*, 
            ps.style_name, 
            ps.thickness_gsm, 
            ps.material,
            (SELECT url FROM product_media WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image,
            (SELECT url FROM product_media WHERE product_id = p.id AND is_primary = 0 ORDER BY sort_order ASC LIMIT 1) AS secondary_image
        FROM products p
        JOIN product_categories pc ON p.id = pc.product_id
        LEFT JOIN product_specifications ps ON p.id = ps.product_id
        WHERE pc.category_id = ? AND p.status = 'active'
        ORDER BY p.id ASC
    ");
    $pStmt->execute([$category['id']]);
    $products = $pStmt->fetchAll();
}

$catBanner = !empty($category['banner_url']) 
    ? $category['banner_url'] 
    : 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=1600&q=80';
?>
<section class="category-hero" style="background-image: linear-gradient(180deg, rgba(11,9,16,0.6) 0%, rgba(11,9,16,0.92) 100%), url('<?= e($catBanner) ?>');">
    <div class="container category-hero__inner">
        <?php if ($category): ?>
            <span class="category-hero__eyebrow"><?= e(__('category_sec_tag')) ?></span>
            <h1 class="category-hero__title"><?= e($category['name']) ?></h1>
            <p class="category-hero__desc"><?= e($category['description']) ?></p>
        <?php else: ?>
            <h1 class="category-hero__title">CATEGORY NOT FOUND</h1>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($category): ?>
            <div class="section-head">
                <div class="section-head__left">
                    <span class="section-eyebrow">ARCHIVE // <?= e(strtoupper($category['slug'])) ?></span>
                    <h2 class="section-title"><?= e(__('new_arrivals_title')) ?></h2>
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
                            <span class="product-card__category"><?= e($category['name']) ?></span>
                            <h3 class="product-card__title">
                                <a href="<?= base_url('product/' . $p['slug']) ?>"><?= e($p['name']) ?></a>
                            </h3>
                            <p class="product-card__desc"><?= e($p['short_description']) ?></p>
                            <div class="product-card__price-row">
                                <span class="price-current"><?= format_price((float)($p['sale_price'] ?? $p['base_price'])) ?></span>
                                <a href="<?= base_url('product/' . $p['slug']) ?>" class="btn btn--sm btn--primary"><?= e(__('view_details')) ?></a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding: 80px 20px;">
                <a href="<?= base_url('shop') ?>" class="btn btn--primary"><?= e(__('continue_shopping')) ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>
