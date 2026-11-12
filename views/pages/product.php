<?php
/**
 * Single Product Detail Page — Amethystclothing.lk
 * Phase 2: Dynamic Variant Selection, Real-Time SKU Barcode & Live Stock Indicators
 */
$slug = $slug ?? '';
$pdo = db();

// Fetch product with specs, categories and size chart
$stmt = $pdo->prepare("
    SELECT p.*, ps.*, b.name AS brand_name, c.name AS category_name, c.slug AS category_slug, 
           sc.measurements_json, sc.title AS size_chart_title, sc.fit_type AS size_chart_fit, sc.notes AS size_chart_notes
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
$sizeChart = null;

if ($product) {
    // Resolve size guide chart
    if (!empty($product['measurements_json'])) {
        $sizeChart = [
            'title' => $product['size_chart_title'] ?? 'Amethyst Fit & Size Guide',
            'fit_type' => $product['size_chart_fit'] ?? 'Regular Fit',
            'notes' => $product['size_chart_notes'] ?? 'All measurements are in inches unless switched to centimeters.',
            'measurements' => json_decode($product['measurements_json'], true) ?: []
        ];
    } else {
        // Fallback to active size chart in DB
        $scStmt = $pdo->query("SELECT * FROM size_charts ORDER BY id ASC LIMIT 1");
        $fallbackSc = $scStmt->fetch();
        if ($fallbackSc) {
            $sizeChart = [
                'title' => $fallbackSc['title'],
                'fit_type' => $fallbackSc['fit_type'],
                'notes' => $fallbackSc['notes'],
                'measurements' => json_decode($fallbackSc['measurements_json'], true) ?: []
            ];
        }
    }
    // Fetch all variants with stock quantity
    $vStmt = $pdo->prepare("SELECT id, sku, color_name, color_hex, size_code, total_stock_quantity, price_override FROM product_variants WHERE product_id = ? ORDER BY sort_order ASC");
    $vStmt->execute([$product['id']]);
    $variants = $vStmt->fetchAll();

    // Fetch product gallery media
    $mStmt = $pdo->prepare("SELECT url, alt_text, is_primary FROM product_media WHERE product_id = ? ORDER BY sort_order ASC");
    $mStmt->execute([$product['id']]);
    $mediaItems = $mStmt->fetchAll();

    // Fetch approved customer reviews
    $rStmt = $pdo->prepare("
        SELECT pr.*, c.full_name as customer_name
        FROM product_reviews pr
        LEFT JOIN customers c ON c.id = pr.customer_id
        WHERE pr.product_id = ? AND pr.status = 'approved'
        ORDER BY pr.created_at DESC
    ");
    $rStmt->execute([$product['id']]);
    $reviews = $rStmt->fetchAll();

    // Check wishlist status
    $inWishlist = in_array((int)$product['id'], $_SESSION['wishlist'] ?? []);
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
                            <div class="variant-group__header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                <div>
                                    <span class="variant-label"><?= e(__('select_size')) ?></span>
                                    <span class="selected-size-name" id="selected-size-label"></span>
                                </div>
                                <?php if ($sizeChart && !empty($sizeChart['measurements'])): ?>
                                    <button type="button" id="btn-open-size-guide" style="background:none; border:none; padding:0; cursor:pointer; font-family:var(--font-mono); font-size:11px; letter-spacing:0.08em; text-transform:uppercase; color:var(--brand-deep); font-weight:700; display:inline-flex; align-items:center; gap:5px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 6H3v12h18V6z"></path><path d="M7 6v4m4-4v2m4-2v4m4-4v2"></path></svg>
                                        <?= e(__('size_guide') ?? 'SIZE & FIT GUIDE') ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="size-selector" id="size-selector-grid">
                                <?php foreach ($variants as $idx => $v): ?>
                                    <button type="button" 
                                            class="size-btn <?= $idx === 0 ? 'is-selected' : '' ?>" 
                                            data-variant-id="<?= (int)$v['id'] ?>"
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

                    <!-- Action Buttons (Add to Bag / Wishlist / Guest Checkout) -->
                    <div class="product-detail__actions">
                        <div style="display:flex;gap:10px;">
                            <button type="button" class="btn btn--brand btn--full" id="add-to-bag-btn" style="flex:1;">
                                <?= e(__('add_to_bag')) ?>
                            </button>
                            <button type="button" class="btn btn--outline" id="btn-product-wishlist" 
                                    data-product-id="<?= (int)$product['id'] ?>"
                                    title="Save to Wishlist"
                                    style="padding: 0 18px; border-radius: 0; display:flex; align-items:center; justify-content:center; <?= $inWishlist ? 'background:var(--brand-deep);color:#FFF;border-color:var(--brand-deep);' : '' ?>">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="<?= $inWishlist ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </button>
                        </div>
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

            <!-- Customer Reviews & Social Proof Section -->
            <div class="product-reviews-container" style="margin-top: 60px; padding-top: 50px; border-top: 1px solid var(--border-crisp);">
                <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 32px; flex-wrap:wrap; gap:16px;">
                    <div>
                        <span style="font-family: var(--font-mono); font-size: 11px; letter-spacing: 0.15em; color: var(--brand-deep); text-transform: uppercase;">
                            VERIFIED BUYER FEEDBACK // SOCIAL PROOF
                        </span>
                        <h2 style="font-size: 24px; font-weight: 800; text-transform: uppercase; margin: 4px 0 0; letter-spacing: -0.01em;">
                            CUSTOMER REVIEWS (<?= count($reviews) ?>)
                        </h2>
                    </div>

                    <?php 
                        $avgRating = $product['average_rating'] ?: 5.0;
                    ?>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="color: #F59E0B; font-size: 18px; letter-spacing: 2px;">
                            ★★★★★
                        </div>
                        <span style="font-family: var(--font-mono); font-weight: 700; font-size: 15px;">
                            <?= number_format((float)$avgRating, 1) ?> / 5.0
                        </span>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1.6fr 1.2fr; gap: 48px; align-items:start;">
                    <!-- Reviews List -->
                    <div>
                        <?php if (empty($reviews)): ?>
                            <div style="padding: 40px; background: #FFFFFF; border: 1px solid var(--border-crisp); text-align: center;">
                                <div style="font-size: 14px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">BE THE FIRST TO REVIEW</div>
                                <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">
                                    Own this silhouette? Leave an honest fit and fabric review to help the Amethyst community.
                                </p>
                            </div>
                        <?php else: ?>
                            <div style="display:flex; flex-direction:column; gap: 20px;">
                                <?php foreach ($reviews as $rev): ?>
                                    <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 24px;">
                                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom: 10px;">
                                            <div>
                                                <div style="color: #F59E0B; font-size: 14px; letter-spacing: 1px; margin-bottom: 4px;">
                                                    <?= str_repeat('★', (int)$rev['rating']) ?><?= str_repeat('☆', 5 - (int)$rev['rating']) ?>
                                                </div>
                                                <h4 style="font-size: 15px; font-weight: 800; text-transform: uppercase; margin: 0; letter-spacing: 0.02em;">
                                                    <?= e($rev['review_title']) ?>
                                                </h4>
                                            </div>
                                            <span style="font-family: var(--font-mono); font-size: 11px; color: #8C869B;">
                                                <?= date('M d, Y', strtotime($rev['created_at'])) ?>
                                            </span>
                                        </div>

                                        <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 14px;">
                                            <?= nl2br(e($rev['review_text'])) ?>
                                        </p>

                                        <div style="display:flex; align-items:center; gap: 12px; font-size: 12px;">
                                            <strong style="color: var(--text-primary);"><?= e($rev['customer_name'] ?: 'Verified Customer') ?></strong>
                                            
                                            <?php if (!empty($rev['is_verified_buyer'])): ?>
                                                <span style="font-family: var(--font-mono); font-size: 10px; color: #065F46; background: #D1FAE5; padding: 2px 6px; letter-spacing: 0.05em;">
                                                    &check; VERIFIED BUYER
                                                </span>
                                            <?php endif; ?>

                                            <?php if (!empty($rev['purchased_size'])): ?>
                                                <span style="font-family: var(--font-mono); font-size: 10px; color: #716B80; background: #F4F2F8; padding: 2px 6px;">
                                                    SIZE: <?= e($rev['purchased_size']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Write a Review Form -->
                    <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 32px;">
                        <h3 style="font-size: 16px; font-weight: 800; text-transform: uppercase; margin: 0 0 6px; letter-spacing: 0.04em;">
                            WRITE A VERIFIED REVIEW
                        </h3>
                        <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 20px;">
                            Share your feedback on the 240GSM cotton weight, silhouette drop, and wash performance.
                        </p>

                        <div id="review-feedback" style="display:none; padding:12px; margin-bottom:16px; font-size:13px; line-height:1.4;"></div>

                        <form id="product-review-form">
                            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                            <!-- Rating Picker -->
                            <div style="margin-bottom: 16px;">
                                <label style="display:block; font-family:var(--font-mono); font-size:10px; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:6px;">
                                    YOUR RATING <span style="color:#EF4444;">*</span>
                                </label>
                                <div style="display:flex; gap:8px;" id="star-picker">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <label style="cursor:pointer; display:flex; align-items:center; gap:2px; font-size:13px; padding:6px 10px; border:1px solid var(--border-crisp);">
                                            <input type="radio" name="rating" value="<?= $s ?>" <?= $s === 5 ? 'checked' : '' ?> style="margin:0;">
                                            <span><?= $s ?> ★</span>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:14px;">
                                <div>
                                    <label style="display:block; font-family:var(--font-mono); font-size:10px; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:6px;">
                                        YOUR NAME <span style="color:#EF4444;">*</span>
                                    </label>
                                    <input type="text" name="reviewer_name" value="<?= e(customer_user()['full_name'] ?? '') ?>" placeholder="e.g. Shanuka K." required
                                           style="width:100%; padding:10px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; box-sizing:border-box;">
                                </div>

                                <div>
                                    <label style="display:block; font-family:var(--font-mono); font-size:10px; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:6px;">
                                        PURCHASED SIZE
                                    </label>
                                    <select name="purchased_size" style="width:100%; padding:10px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; background:#FFF; box-sizing:border-box;">
                                        <option value="S">Small (S)</option>
                                        <option value="M" selected>Medium (M)</option>
                                        <option value="L">Large (L)</option>
                                        <option value="XL">X-Large (XL)</option>
                                        <option value="XXL">2X-Large (XXL)</option>
                                    </select>
                                </div>
                            </div>

                            <div style="margin-bottom:14px;">
                                <label style="display:block; font-family:var(--font-mono); font-size:10px; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:6px;">
                                    MOBILE NUMBER (FOR BUYER VERIFICATION)
                                </label>
                                <input type="tel" name="reviewer_phone" value="<?= e(customer_user()['phone'] ?? '') ?>" placeholder="07XXXXXXXX"
                                       style="width:100%; padding:10px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; box-sizing:border-box;">
                            </div>

                            <div style="margin-bottom:14px;">
                                <label style="display:block; font-family:var(--font-mono); font-size:10px; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:6px;">
                                    REVIEW HEADLINE <span style="color:#EF4444;">*</span>
                                </label>
                                <input type="text" name="review_title" placeholder="e.g. Best drop shoulder fit in Sri Lanka" required
                                       style="width:100%; padding:10px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; box-sizing:border-box;">
                            </div>

                            <div style="margin-bottom:18px;">
                                <label style="display:block; font-family:var(--font-mono); font-size:10px; letter-spacing:0.1em; text-transform:uppercase; margin-bottom:6px;">
                                    DETAILED REVIEW <span style="color:#EF4444;">*</span>
                                </label>
                                <textarea name="review_text" rows="4" placeholder="How is the collar tightness, heavy cotton feel, and print longevity?" required
                                          style="width:100%; padding:10px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; box-sizing:border-box;"></textarea>
                            </div>

                            <button type="submit" id="btn-submit-review" class="btn btn--brand btn--full" style="padding:12px; font-size:12px; letter-spacing:0.1em; border-radius:0;">
                                SUBMIT REVIEW FOR VERIFICATION &rarr;
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Pass Variant Data to Frontend for Vanilla JS Reactive Engine -->
            <script id="product-variants-json" type="application/json">
                <?= json_encode($variants) ?>
            </script>

            <script>
                // 1. Wishlist Toggle Button Handler
                const wishlistBtn = document.getElementById('btn-product-wishlist');
                if (wishlistBtn) {
                    wishlistBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const pId = this.dataset.productId;
                        const svg = this.querySelector('svg');
                        
                        const formData = new FormData();
                        formData.append('product_id', pId);
                        formData.append('action', 'toggle');

                        fetch('<?= base_url('ajax/wishlist.php') ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // Update header badge
                                const badge = document.getElementById('wishlist-badge');
                                if (badge) badge.textContent = data.count;

                                if (data.in_wishlist) {
                                    this.style.background = 'var(--brand-deep)';
                                    this.style.color = '#FFFFFF';
                                    this.style.borderColor = 'var(--brand-deep)';
                                    if (svg) svg.setAttribute('fill', '#FFFFFF');
                                } else {
                                    this.style.background = '';
                                    this.style.color = '';
                                    this.style.borderColor = '';
                                    if (svg) svg.setAttribute('fill', 'none');
                                }
                            }
                        })
                        .catch(err => console.error(err));
                    });
                }

                // 2. Submit Review Form Handler
                const reviewForm = document.getElementById('product-review-form');
                if (reviewForm) {
                    reviewForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const btn = document.getElementById('btn-submit-review');
                        const fb = document.getElementById('review-feedback');

                        fb.style.display = 'none';
                        btn.disabled = true;
                        btn.textContent = 'SUBMITTING...';

                        const formData = new FormData(this);

                        fetch('<?= base_url('ajax/submit_review.php') ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            btn.disabled = false;
                            btn.textContent = 'SUBMIT REVIEW FOR VERIFICATION →';

                            if (data.success) {
                                fb.style.background = '#D1FAE5';
                                fb.style.border = '1px solid #A7F3D0';
                                fb.style.color = '#065F46';
                                fb.textContent = data.message;
                                fb.style.display = 'block';
                                reviewForm.reset();
                            } else {
                                fb.style.background = '#FEE2E2';
                                fb.style.border = '1px solid #FECACA';
                                fb.style.color = '#991B1B';
                                fb.textContent = data.message || 'Error submitting review.';
                                fb.style.display = 'block';
                            }
                        })
                        .catch(err => {
                            btn.disabled = false;
                            btn.textContent = 'SUBMIT REVIEW FOR VERIFICATION →';
                            fb.style.background = '#FEE2E2';
                            fb.style.border = '1px solid #FECACA';
                            fb.style.color = '#991B1B';
                            fb.textContent = 'Network error submitting review.';
                            fb.style.display = 'block';
                        });
                    });
                }

                // 3. Size Guide Modal & Unit Conversion Engine
                const openSizeGuideBtn = document.getElementById('btn-open-size-guide');
                const closeSizeGuideBtn = document.getElementById('btn-close-size-guide');
                const sizeGuideModal = document.getElementById('size-guide-modal');
                const sizeGuideOverlay = document.getElementById('size-guide-overlay');
                const unitInchBtn = document.getElementById('unit-inch-btn');
                const unitCmBtn = document.getElementById('unit-cm-btn');

                function toggleSizeGuideModal(show) {
                    if (!sizeGuideModal) return;
                    if (show) {
                        sizeGuideModal.classList.add('is-open');
                        document.body.style.overflow = 'hidden';
                    } else {
                        sizeGuideModal.classList.remove('is-open');
                        document.body.style.overflow = '';
                    }
                }

                if (openSizeGuideBtn) {
                    openSizeGuideBtn.addEventListener('click', () => toggleSizeGuideModal(true));
                }
                if (closeSizeGuideBtn) {
                    closeSizeGuideBtn.addEventListener('click', () => toggleSizeGuideModal(false));
                }
                if (sizeGuideOverlay) {
                    sizeGuideOverlay.addEventListener('click', () => toggleSizeGuideModal(false));
                }
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && sizeGuideModal && sizeGuideModal.classList.contains('is-open')) {
                        toggleSizeGuideModal(false);
                    }
                });

                function setMeasurementUnit(unit) {
                    if (!sizeGuideModal) return;
                    const unitLabels = sizeGuideModal.querySelectorAll('.unit-label');
                    const chestCells = sizeGuideModal.querySelectorAll('.val-chest');
                    const lengthCells = sizeGuideModal.querySelectorAll('.val-length');
                    const shoulderCells = sizeGuideModal.querySelectorAll('.val-shoulder');

                    if (unit === 'cm') {
                        if (unitCmBtn) {
                            unitCmBtn.classList.add('is-active');
                            unitCmBtn.style.background = 'var(--brand-deep)';
                            unitCmBtn.style.color = '#FFFFFF';
                        }
                        if (unitInchBtn) {
                            unitInchBtn.classList.remove('is-active');
                            unitInchBtn.style.background = 'transparent';
                            unitInchBtn.style.color = 'var(--text-primary)';
                        }

                        unitLabels.forEach(el => el.textContent = 'CM');

                        const convert = (cells) => {
                            cells.forEach(td => {
                                const inch = parseFloat(td.dataset.valInch) || 0;
                                td.textContent = (inch * 2.54).toFixed(1) + ' cm';
                            });
                        };
                        convert(chestCells);
                        convert(lengthCells);
                        convert(shoulderCells);
                    } else {
                        if (unitInchBtn) {
                            unitInchBtn.classList.add('is-active');
                            unitInchBtn.style.background = 'var(--brand-deep)';
                            unitInchBtn.style.color = '#FFFFFF';
                        }
                        if (unitCmBtn) {
                            unitCmBtn.classList.remove('is-active');
                            unitCmBtn.style.background = 'transparent';
                            unitCmBtn.style.color = 'var(--text-primary)';
                        }

                        unitLabels.forEach(el => el.textContent = 'IN');

                        const convert = (cells) => {
                            cells.forEach(td => {
                                const inch = parseFloat(td.dataset.valInch) || 0;
                                td.textContent = inch + ' in';
                            });
                        };
                        convert(chestCells);
                        convert(lengthCells);
                        convert(shoulderCells);
                    }
                }

                if (unitInchBtn && unitCmBtn) {
                    unitInchBtn.addEventListener('click', () => setMeasurementUnit('inch'));
                    unitCmBtn.addEventListener('click', () => setMeasurementUnit('cm'));
                }
            </script>

            <?php if ($sizeChart && !empty($sizeChart['measurements'])): ?>
                <!-- Interactive Size Guide Modal -->
                <div class="modal" id="size-guide-modal" aria-hidden="true">
                    <div class="modal__overlay" id="size-guide-overlay"></div>
                    <div class="modal__panel" style="width: 580px; max-width: 95vw; max-height: 90vh; overflow-y: auto;">
                        <div class="modal__head" style="background: #FAF9FC; border-bottom: 1px solid var(--border-crisp);">
                            <div>
                                <span style="font-family: var(--font-mono); font-size: 10px; color: var(--brand-deep); letter-spacing: 0.12em; text-transform: uppercase;">
                                    GARMENT SPECIFICATION // FIT GUIDE
                                </span>
                                <h3 class="modal__title" style="margin: 4px 0 0; font-size: 16px;"><?= e($sizeChart['title']) ?></h3>
                            </div>
                            <button type="button" class="modal__close" id="btn-close-size-guide" aria-label="Close" style="background:none; border:none; cursor:pointer; font-size:24px;">&times;</button>
                        </div>
                        <div class="modal__body" style="padding: 24px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 18px; flex-wrap:wrap; gap:12px;">
                                <div style="display:inline-flex; align-items:center; gap:8px;">
                                    <span style="font-family: var(--font-mono); font-size: 11px; text-transform: uppercase; color: #716B80;">FIT PROFILE:</span>
                                    <span style="font-family: var(--font-mono); font-size: 11px; font-weight: 700; background: var(--brand-deep); color: #FFFFFF; padding: 2px 8px; text-transform: uppercase;">
                                        <?= e($sizeChart['fit_type']) ?>
                                    </span>
                                </div>
                                
                                <!-- Unit Switcher (Inches / CM) -->
                                <div style="display:flex; align-items:center; gap:4px; border: 1px solid var(--border-crisp); padding: 2px; background: #FAF9FC;">
                                    <button type="button" id="unit-inch-btn" class="unit-toggle-btn is-active" style="padding: 4px 10px; font-family: var(--font-mono); font-size: 11px; font-weight: 700; border: none; cursor: pointer; background: var(--brand-deep); color: #FFFFFF;">
                                        INCHES
                                    </button>
                                    <button type="button" id="unit-cm-btn" class="unit-toggle-btn" style="padding: 4px 10px; font-family: var(--font-mono); font-size: 11px; font-weight: 700; border: none; cursor: pointer; background: transparent; color: var(--text-primary);">
                                        CM
                                    </button>
                                </div>
                            </div>

                            <!-- Matrix Table -->
                            <div style="overflow-x: auto; border: 1px solid var(--border-crisp);">
                                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12px;">
                                    <thead>
                                        <tr style="background: #0B0910; color: #FFFFFF; font-family: var(--font-mono); text-transform: uppercase; font-size: 11px; letter-spacing: 0.08em;">
                                            <th style="padding: 10px 14px; border-right: 1px solid #23202E;">SIZE</th>
                                            <th style="padding: 10px 14px; border-right: 1px solid #23202E;">CHEST (<span class="unit-label">IN</span>)</th>
                                            <th style="padding: 10px 14px; border-right: 1px solid #23202E;">LENGTH (<span class="unit-label">IN</span>)</th>
                                            <th style="padding: 10px 14px;">SHOULDER (<span class="unit-label">IN</span>)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sizeChart['measurements'] as $row): 
                                            $size = $row['size'] ?? '';
                                            $chest = (float)($row['chest_inch'] ?? $row['chest'] ?? 0);
                                            $len = (float)($row['length_inch'] ?? $row['length'] ?? 0);
                                            $shld = (float)($row['shoulder_inch'] ?? $row['shoulder'] ?? 0);
                                        ?>
                                            <tr style="border-bottom: 1px solid var(--border-crisp); font-family: var(--font-mono);">
                                                <td style="padding: 10px 14px; font-weight: 800; background: #FAF9FC; border-right: 1px solid var(--border-crisp);"><?= e($size) ?></td>
                                                <td style="padding: 10px 14px; border-right: 1px solid var(--border-crisp);" class="val-chest" data-val-inch="<?= $chest ?>"><?= $chest ?> in</td>
                                                <td style="padding: 10px 14px; border-right: 1px solid var(--border-crisp);" class="val-length" data-val-inch="<?= $len ?>"><?= $len ?> in</td>
                                                <td style="padding: 10px 14px;" class="val-shoulder" data-val-inch="<?= $shld ?>"><?= $shld ?> in</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if (!empty($sizeChart['notes'])): ?>
                                <div style="margin-top: 16px; padding: 12px 14px; background: #FAF9FC; border-left: 3px solid var(--brand-deep); font-size: 12px; color: var(--text-secondary); line-height: 1.5;">
                                    <strong style="color: var(--text-primary); text-transform: uppercase; font-family: var(--font-mono); font-size: 10px; display: block; margin-bottom: 4px;">FIT NOTES & SIZING RECOMMENDATION:</strong>
                                    <?= nl2br(e($sizeChart['notes'])) ?>
                                </div>
                            <?php endif; ?>

                            <div style="margin-top: 16px; font-size: 11px; color: #8C869B; font-family: var(--font-mono); line-height: 1.5;">
                                &bull; Chest: Measure around fullest part of chest, tape horizontal.<br>
                                &bull; Length: Measure from highest point of shoulder straight to hem.<br>
                                &bull; Shoulder: Measure from shoulder tip across back to opposite shoulder tip.
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div style="text-align:center; padding: 80px 20px;">
                <h2 style="font-family: 'Archivo Black', sans-serif; font-size: 32px;">PRODUCT NOT FOUND</h2>
                <a href="<?= base_url('shop') ?>" class="btn btn--primary" style="margin-top: 24px;"><?= e(__('continue_shopping')) ?></a>
            </div>
        <?php endif; ?>
    </div>
</section>
