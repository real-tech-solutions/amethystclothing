<?php
/**
 * Single Product Detail Page — Amethystclothing.lk
 * Phase 2: Dynamic Variant Selection, Real-Time SKU Barcode & Live Stock Indicators
 */
$slug = $slug ?? '';
$pdo = db();

// Fetch product with specs, categories and size chart
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
$mediaItems = [];

if ($product) {
    // Fetch all variants with stock quantity
    $vStmt = $pdo->prepare("SELECT id, sku, color_name, color_hex, size_code, total_stock_quantity, price_override FROM product_variants WHERE product_id = ? ORDER BY sort_order ASC");
    $vStmt->execute([$product['id']]);
    $variants = $vStmt->fetchAll();

    // Fetch product gallery media
    $mStmt = $pdo->prepare("SELECT url, alt_text, is_primary FROM product_media WHERE product_id = ? ORDER BY sort_order ASC");
    $mStmt->execute([$product['id']]);
    $mediaItems = $mStmt->fetchAll();
}

$pageTitle = $product ? e($product['name']) . ' — Amethystclothing.lk' : 'Product — Amethystclothing.lk';

$primaryImage = !empty($mediaItems[0]['url']) 
    ? $mediaItems[0]['url'] 
    : 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=800&h=1000&q=80';
?>
<section class="section product-detail-section">
    <div class="container">
        <?php if ($product): ?>
            <!-- Breadcrumbs -->
            <nav class="breadcrumb-nav">
                <a href="<?= base_url() ?>">HOME</a>
                <span>/</span>
                <a href="<?= base_url('shop') ?>"><?= e(__('nav_shop_all')) ?></a>
                <span>/</span>
                <?php if (!empty($product['category_name'])): ?>
                    <a href="<?= base_url('category/' . $product['category_slug']) ?>"><?= e(strtoupper($product['category_name'])) ?></a>
                    <span>/</span>
                <?php endif; ?>
                <strong><?= e(strtoupper($product['name'])) ?></strong>
            </nav>

            <div class="product-detail-layout">
                <!-- Visual & Gallery Column -->
                <div class="product-detail__media-wrapper">
                    <div class="product-detail__main-image-box">
                        <img id="main-product-image" src="<?= e($primaryImage) ?>" alt="<?= e($product['name']) ?>" class="product-detail__main-image">
                        <div class="product-card__tech-tag">
                            <span><?= e($product['thickness_gsm'] ?? '200 GSM') ?></span>
                        </div>
                    </div>

                    <?php if (count($mediaItems) > 1): ?>
                        <div class="product-detail__thumbnails">
                            <?php foreach ($mediaItems as $idx => $m): ?>
                                <button type="button" class="thumb-btn <?= $idx === 0 ? 'is-active' : '' ?>" data-src="<?= e($m['url']) ?>">
                                    <img src="<?= e($m['url']) ?>" alt="<?= e($m['alt_text'] ?? $product['name']) ?>" loading="lazy">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Authentic Barcode Tag STRICTLY For This Product SKU -->
                    <div class="product-sku-barcode-panel">
                        <div class="barcode-display-wrapper">
                            <span class="product-live-barcode" id="product-live-barcode">*<?= e($variants[0]['sku'] ?? $product['base_sku_prefix']) ?>*</span>
                        </div>
                        <div class="barcode-legend">
                            <span class="legend-sku" id="barcode-sku-label"><?= e($variants[0]['sku'] ?? $product['base_sku_prefix']) ?></span>
                            <span class="legend-text">OFFICIAL GARMENT SPECIFICATION &bull; AMETHYST LK</span>
                        </div>
                    </div>
                </div>

                <!-- Product Information & Live Variant Action Column -->
                <div class="product-detail__info">
                    <div class="product-detail__header">
                        <div class="product-detail__sku-row">
                            <span class="sku-tag" id="current-sku-text">SKU: <?= e($variants[0]['sku'] ?? $product['base_sku_prefix']) ?></span>
                            
                            <!-- Dynamic Live Stock Status Badge -->
                            <span class="stock-badge stock-badge--in-stock" id="stock-status-badge">
                                <?= e(__('in_stock')) ?>
                            </span>

                            <?php if (!empty($product['badge_text'])): ?>
                                <span class="product-badge product-badge--<?= strtolower(str_replace(' ', '-', $product['badge_text'])) ?>">
                                    <?= e($product['badge_text']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h1 class="product-detail__title"><?= e($product['name']) ?></h1>
                        
                        <!-- Pricing Box -->
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

                    <!-- Dynamic Size Selector (Reacts via JS) -->
                    <div class="product-detail__variants">
                        <div class="variant-group">
                            <div class="variant-group__header">
                                <span class="variant-label"><?= e(__('select_size')) ?></span>
                                <span class="selected-size-name" id="selected-size-label"></span>
                            </div>
                            <div class="size-selector" id="size-selector-grid">
                                <?php foreach ($variants as $idx => $v): ?>
                                    <button type="button" 
                                            class="size-btn <?= $idx === 0 ? 'is-selected' : '' ?>" 
                                            data-sku="<?= e($v['sku']) ?>"
                                            data-size="<?= e($v['size_code']) ?>"
                                            data-stock="<?= (int)$v['total_stock_quantity'] ?>"
                                            data-color="<?= e($v['color_name']) ?>">
                                        <?= e($v['size_code']) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons (Add to Bag / Guest Checkout) -->
                    <div class="product-detail__actions">
                        <button type="button" class="btn btn--brand btn--full" id="add-to-bag-btn">
                            <?= e(__('add_to_bag')) ?>
                        </button>
                        <a href="<?= base_url('checkout') ?>" class="btn btn--outline btn--full" id="buy-now-btn" style="margin-top: 10px;">
                            <?= e(__('buy_now_guest')) ?>
                        </a>
                    </div>

                    <!-- Low Stock Alert Warning Box -->
                    <div class="stock-warning-box" id="stock-warning-box" style="display: none;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <span id="stock-warning-text"></span>
                    </div>

                    <!-- Value Highlights -->
                    <div class="product-perks-box">
                        <div class="perk-item">
                            <span class="perk-dot"></span>
                            <span><strong>Islandwide Delivery:</strong> 1-3 Days Colombo, 3-5 Days Outstation</span>
                        </div>
                        <div class="perk-item">
                            <span class="perk-dot"></span>
                            <span><strong>14-Day Size Exchange:</strong> Hassle-free doorstep exchange service</span>
                        </div>
                        <div class="perk-item">
                            <span class="perk-dot"></span>
                            <span><strong>Payment:</strong> Cash on Delivery, Visa/Mastercard, Koko / Mintpay</span>
                        </div>
                    </div>

                    <!-- BEAST-Style Specifications Tech Table -->
                    <div class="spec-table-box">
                        <h4 class="spec-table-box__title"><?= e(__('tech_specs_title')) ?></h4>
                        <table class="spec-table">
                            <tbody>
                                <tr><th><?= e(__('material_label')) ?></th><td><?= e($product['material'] ?? '100% Combed Cotton') ?></td></tr>
                                <tr><th><?= e(__('gsm_label')) ?></th><td><?= e($product['thickness_gsm'] ?? '200 GSM') ?></td></tr>
                                <tr><th><?= e(__('colour_label')) ?></th><td><?= e($product['colour_label'] ?? 'Jet Black') ?></td></tr>
                                <tr><th><?= e(__('fit_label')) ?></th><td><?= e($product['style_name'] ?? 'Regular / Drop Shoulder') ?></td></tr>
                                <tr><th><?= e(__('standards_label')) ?></th><td><?= e($product['quality_standards'] ?? '100% QC Passed. Export Ready.') ?></td></tr>
                                <tr><th><?= e(__('specialities_label')) ?></th><td><?= e($product['specialities'] ?? 'Tagless. Softened. Anti-Shrink.') ?></td></tr>
                                <tr><th><?= e(__('exchange_label')) ?></th><td><?= e($product['warranty_info'] ?? '14-Day Doorstep Size Exchange.') ?></td></tr>
                                <tr><th><?= e(__('delivery_label')) ?></th><td><?= e($product['delivery_estimate'] ?? '1-3 Working Days Colombo. 3-5 Outstation.') ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pass Variant Data to Frontend for Vanilla JS Reactive Engine -->
            <script id="product-variants-json" type="application/json">
                <?= json_encode($variants) ?>
            </script>
        <?php else: ?>
            <div style="text-align:center; padding: 80px 20px;">
                <h2 style="font-family: 'Archivo Black', sans-serif; font-size: 32px;">PRODUCT NOT FOUND</h2>
                <a href="<?= base_url('shop') ?>" class="btn btn--primary" style="margin-top: 24px;"><?= e(__('continue_shopping')) ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>
