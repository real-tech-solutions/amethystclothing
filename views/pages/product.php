<?php
/**
 * Single Product Detail Shell — Amethystclothing.lk
 */
$slug = $slug ?? '';
$pdo = db();

$stmt = $pdo->prepare("
    SELECT p.*, ps.*, b.name AS brand_name, c.name AS category_name, c.slug AS category_slug, sc.measurements_json, sc.title AS size_chart_title
    FROM products p
    LEFT JOIN product_specifications ps ON p.id = ps.product_id
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN product_categories pc ON p.id = pc.product_id
    LEFT JOIN categories c ON pc.category_id = c.id
    LEFT JOIN size_charts sc ON p.size_chart_id = sc.id
    WHERE p.slug = ? AND p.status = 'active'
");
$stmt->execute([$slug]);
$product = $stmt->fetch();

$variants = [];
if ($product) {
    $vStmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY sort_order ASC");
    $vStmt->execute([$product['id']]);
    $variants = $vStmt->fetchAll();
}

$pageTitle = $product ? e($product['name']) . ' — Amethystclothing.lk' : 'Product — Amethystclothing.lk';
?>
<section class="section product-detail-section">
    <div class="container">
        <?php if ($product): ?>
            <!-- Breadcrumbs -->
            <nav class="breadcrumb-nav">
                <a href="<?= base_url() ?>">HOME</a>
                <span>/</span>
                <a href="<?= base_url('shop') ?>">ARCHIVE</a>
                <span>/</span>
                <?php if (!empty($product['category_name'])): ?>
                    <a href="<?= base_url('category/' . $product['category_slug']) ?>"><?= e(strtoupper($product['category_name'])) ?></a>
                    <span>/</span>
                <?php endif; ?>
                <strong><?= e(strtoupper($product['name'])) ?></strong>
            </nav>

            <div class="product-detail-layout">
                <!-- Visual / Mock Column -->
                <div class="product-detail__media">
                    <div class="product-detail__mock">
                        <span class="product-mock-display__watermark">AMETHYST</span>
                        <div class="product-mock-display__hero-info">
                            <span class="hero-style"><?= e($product['style_name'] ?? 'STREETWEAR') ?></span>
                            <span class="hero-gsm"><?= e($product['thickness_gsm'] ?? '200 GSM') ?></span>
                        </div>
                    </div>
                    <div class="product-barcode-box">
                        <span class="barcode-tag">*<?= e($product['base_sku_prefix']) ?>*</span>
                        <span class="barcode-sub"><?= e($product['base_sku_prefix']) ?> // AUTHENTIC STREETWEAR GARMENT</span>
                    </div>
                </div>

                <!-- Info & Purchase Column -->
                <div class="product-detail__info">
                    <div class="product-detail__header">
                        <div class="product-detail__sku-row">
                            <span class="sku-tag">SKU: <?= e($product['base_sku_prefix']) ?></span>
                            <?php if (!empty($product['badge_text'])): ?>
                                <span class="product-badge"><?= e($product['badge_text']) ?></span>
                            <?php endif; ?>
                        </div>
                        <h1 class="product-detail__title"><?= e($product['name']) ?></h1>
                        
                        <div class="product-detail__price-box">
                            <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['base_price']): ?>
                                <span class="price-current price-current--lg"><?= format_price((float)$product['sale_price']) ?></span>
                                <span class="price-base price-base--lg"><?= format_price((float)$product['base_price']) ?></span>
                                <span class="price-save">SAVE <?= format_price((float)($product['base_price'] - $product['sale_price'])) ?></span>
                            <?php else: ?>
                                <span class="price-current price-current--lg"><?= format_price((float)$product['base_price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <p class="product-detail__short-desc"><?= e($product['short_description']) ?></p>

                    <!-- Variants Selection -->
                    <div class="product-detail__variants">
                        <div class="variant-group">
                            <span class="variant-label">SELECT SIZE:</span>
                            <div class="size-selector">
                                <?php foreach ($variants as $v): ?>
                                    <button type="button" class="size-btn" data-sku="<?= e($v['sku']) ?>">
                                        <?= e($v['size_code']) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="product-detail__actions">
                        <button type="button" class="btn btn--primary btn--full" id="add-to-bag-btn">
                            ADD TO BAG
                        </button>
                        <a href="<?= base_url('checkout') ?>" class="btn btn--outline btn--full" style="margin-top: 10px;">
                            BUY NOW VIA GUEST CHECKOUT
                        </a>
                    </div>

                    <!-- BEAST-Style Specifications Accordion -->
                    <div class="spec-table-box">
                        <h4 class="spec-table-box__title">GARMENT SPECIFICATIONS // TECH SHEET</h4>
                        <table class="spec-table">
                            <tbody>
                                <tr><th>MATERIAL</th><td><?= e($product['material'] ?? '100% Combed Cotton') ?></td></tr>
                                <tr><th>GSM / WEIGHT</th><td><?= e($product['thickness_gsm'] ?? '200 GSM') ?></td></tr>
                                <tr><th>COLOUR</th><td><?= e($product['colour_label'] ?? 'Jet Black') ?></td></tr>
                                <tr><th>FIT TYPE</th><td><?= e($product['style_name'] ?? 'Regular / Drop Shoulder') ?></td></tr>
                                <tr><th>STANDARDS</th><td><?= e($product['quality_standards'] ?? '100% QC Passed. Export Ready.') ?></td></tr>
                                <tr><th>SPECIALITIES</th><td><?= e($product['specialities'] ?? 'Tagless. Softened. Anti-Shrink.') ?></td></tr>
                                <tr><th>EXCHANGE</th><td><?= e($product['warranty_info'] ?? '14-Day Doorstep Size Exchange.') ?></td></tr>
                                <tr><th>DELIVERY</th><td><?= e($product['delivery_estimate'] ?? '1-3 Working Days Colombo. 3-5 Outstation.') ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding: 80px 20px;">
                <h2 style="font-family: 'Archivo Black', sans-serif; font-size: 32px;">PRODUCT NOT FOUND</h2>
                <a href="<?= base_url('shop') ?>" class="btn btn--primary" style="margin-top: 24px;">BACK TO SHOP</a>
            </div>
        <?php endif; ?>
    </div>
</section>
