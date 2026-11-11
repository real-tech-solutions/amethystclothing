<?php
/**
 * Category Page — Amethystclothing.lk
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
        SELECT p.*, ps.style_name, ps.thickness_gsm, ps.material 
        FROM products p
        JOIN product_categories pc ON p.id = pc.product_id
        LEFT JOIN product_specifications ps ON p.id = ps.product_id
        WHERE pc.category_id = ? AND p.status = 'active'
        ORDER BY p.id ASC
    ");
    $pStmt->execute([$category['id']]);
    $products = $pStmt->fetchAll();
}
?>
<section class="section">
    <div class="container">
        <?php if ($category): ?>
            <div class="section-head">
                <div class="section-head__left">
                    <span class="section-eyebrow">COLLECTION // <?= e(strtoupper($category['slug'])) ?></span>
                    <h1 class="section-title"><?= e($category['name']) ?></h1>
                    <p class="section-subtitle"><?= e($category['description']) ?></p>
                </div>
                <div class="section-head__right">
                    <span class="product-count-badge"><?= count($products) ?> ITEMS</span>
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
                            <h3 class="product-card__title">
                                <a href="<?= base_url('product/' . $p['slug']) ?>"><?= e($p['name']) ?></a>
                            </h3>
                            <p class="product-card__desc"><?= e($p['short_description']) ?></p>
                            <div class="product-card__price-row">
                                <span class="price-current"><?= format_price((float)($p['sale_price'] ?? $p['base_price'])) ?></span>
                                <a href="<?= base_url('product/' . $p['slug']) ?>" class="btn btn--sm btn--primary">DETAILS</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding: 80px 20px;">
                <h2 style="font-family: 'Archivo Black', sans-serif; font-size: 32px;">CATEGORY NOT FOUND</h2>
                <a href="<?= base_url('shop') ?>" class="btn btn--primary" style="margin-top: 24px;">BACK TO SHOP</a>
            </div>
        <?php endif; ?>
    </div>
</section>
