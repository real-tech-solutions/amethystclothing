<?php
/**
 * Shop Archive Page — Amethystclothing.lk
 */
$pageTitle = 'Shop All Streetwear — Amethystclothing.lk';

$pdo = db();
$products = $pdo->query("
    SELECT p.*, ps.style_name, ps.thickness_gsm, ps.material, c.name AS category_name 
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
                <span class="section-eyebrow">ARCHIVE CATALOG</span>
                <h1 class="section-title">ALL STREETWEAR EDITIONS</h1>
            </div>
            <div class="section-head__right">
                <span class="product-count-badge"><?= count($products) ?> ITEMS FOUND</span>
            </div>
        </div>

        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <article class="product-card">
                    <div class="product-card__meta-bar">
                        <span class="product-card__sku"><?= e($p['base_sku_prefix']) ?></span>
                        <?php if (!empty($p['badge_text'])): ?>
                            <span class="product-badge"><?= e($p['badge_text']) ?></span>
                        <?php endif; ?>
                    </div>
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
                    <div class="product-card__body">
                        <span class="product-card__category"><?= e($p['category_name'] ?? 'Collection') ?></span>
                        <h3 class="product-card__title">
                            <a href="<?= base_url('product/' . $p['slug']) ?>"><?= e($p['name']) ?></a>
                        </h3>
                        <p class="product-card__desc"><?= e($p['short_description']) ?></p>
                        <div class="product-card__price-row">
                            <div class="product-card__prices">
                                <span class="price-current"><?= format_price((float)($p['sale_price'] ?? $p['base_price'])) ?></span>
                            </div>
                            <a href="<?= base_url('product/' . $p['slug']) ?>" class="btn btn--sm btn--primary">DETAILS</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
