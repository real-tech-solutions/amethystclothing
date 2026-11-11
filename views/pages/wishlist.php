<?php
/**
 * Amethystclothing.lk — Wishlist Page
 * Sharp Minimalist Luxury UI — Strictly No AI Slop
 */

$pageTitle = 'Saved Wishlist // AMETHYST';

$wishlistIds = $_SESSION['wishlist'] ?? [];
$wishlistProducts = [];

if (!empty($wishlistIds)) {
    // Sanitize integer IDs
    $ids = array_map('intval', $wishlistIds);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    try {
        $stmt = db()->prepare("
            SELECT p.*, 
                   (SELECT url FROM product_media pm WHERE pm.product_id = p.id AND pm.is_primary = 1 LIMIT 1) as primary_image,
                   (SELECT name FROM categories c JOIN product_categories pc ON pc.category_id = c.id WHERE pc.product_id = p.id LIMIT 1) as category_name
            FROM products p
            WHERE p.id IN ({$placeholders}) AND p.status = 'active'
        ");
        $stmt->execute($ids);
        $wishlistProducts = $stmt->fetchAll();
    } catch (Exception $e) {
        $wishlistProducts = [];
    }
}
?>

<section class="section wishlist-section" style="padding: 60px 0 100px;">
    <div class="container">
        <!-- Section Header -->
        <div class="section-head" style="margin-bottom: 36px; border-bottom: 1px solid var(--border-crisp); padding-bottom: 20px;">
            <div class="section-head__left">
                <span class="section-eyebrow" style="font-family: var(--font-mono); font-size: 11px; letter-spacing: 0.15em; color: var(--brand-deep); text-transform: uppercase;">
                    PERSONAL ARCHIVE // SAVED PIECES
                </span>
                <h1 class="section-title" style="font-size: 28px; font-weight: 800; letter-spacing: -0.02em; margin-top: 6px;">
                    MY WISHLIST
                </h1>
            </div>
            <div class="section-head__right">
                <span class="product-count-badge" id="wishlist-page-count" style="font-family: var(--font-mono); font-size: 11px; background: var(--bg-dark); color: #FFF; padding: 4px 10px;">
                    <?= count($wishlistProducts) ?> SAVED ITEM<?= count($wishlistProducts) === 1 ? '' : 'S' ?>
                </span>
            </div>
        </div>

        <?php if (empty($wishlistProducts)): ?>
            <div class="wishlist-empty-box" style="text-align: center; padding: 90px 20px; background: #FFFFFF; border: 1px solid var(--border-crisp);">
                <div style="width: 56px; height: 56px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; background: rgba(78, 42, 132, 0.08); color: var(--brand-deep);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                    </svg>
                </div>
                <h2 style="font-family: var(--font-display); font-size: 20px; font-weight: 800; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.04em;">
                    YOUR WISHLIST IS CURRENTLY EMPTY
                </h2>
                <p style="font-size: 14px; color: var(--text-secondary); max-width: 460px; margin: 0 auto 28px; line-height: 1.5;">
                    Save your favourite oversized fits, luxury graphic tees, and heavyweight silhouettes here to monitor stock and order quickly.
                </p>
                <a href="<?= base_url('shop') ?>" class="btn btn--brand" style="padding: 14px 28px; font-size: 13px; letter-spacing: 0.1em; border-radius: 0;">
                    EXPLORE STREETWEAR DROPS &rarr;
                </a>
            </div>
        <?php else: ?>
            <div class="wishlist-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
                <?php foreach ($wishlistProducts as $prod): ?>
                    <?php 
                        $img = !empty($prod['primary_image']) ? $prod['primary_image'] : asset_url('images/hero_1.png');
                    ?>
                    <div class="wishlist-card" id="wishlist-item-<?= $prod['id'] ?>" style="background: #FFFFFF; border: 1px solid var(--border-crisp); display: flex; flex-direction: column; position: relative;">
                        <!-- Remove Button -->
                        <button type="button" class="btn-remove-wishlist" data-product-id="<?= $prod['id'] ?>" 
                                title="Remove from wishlist"
                                style="position: absolute; top: 12px; right: 12px; z-index: 10; width: 32px; height: 32px; background: rgba(11,9,16,0.85); color: #FFF; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s;">
                            &times;
                        </button>

                        <!-- Product Thumbnail -->
                        <a href="<?= base_url('product/' . $prod['slug']) ?>" style="display: block; aspect-ratio: 4/5; overflow: hidden; background: #F4F2F8;">
                            <img src="<?= e($img) ?>" alt="<?= e($prod['name']) ?>" style="width: 100%; height: 100%; object-fit: cover; object-position: center; transition: transform 0.3s ease;">
                        </a>

                        <!-- Details Body -->
                        <div style="padding: 20px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                    <span style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-secondary);">
                                        <?= e($prod['category_name'] ?? 'STREETWEAR') ?>
                                    </span>
                                    <span style="font-family: var(--font-mono); font-size: 10px; color: var(--brand-deep); background: rgba(78,42,132,0.08); padding: 2px 6px;">
                                        <?= e($prod['base_sku_prefix']) ?>
                                    </span>
                                </div>
                                <h3 style="font-size: 16px; font-weight: 700; text-transform: uppercase; margin: 0 0 10px; letter-spacing: 0.02em;">
                                    <a href="<?= base_url('product/' . $prod['slug']) ?>" style="color: var(--text-primary); text-decoration: none;">
                                        <?= e($prod['name']) ?>
                                    </a>
                                </h3>
                                <div style="font-family: var(--font-mono); font-weight: 700; font-size: 15px; color: var(--text-primary); margin-bottom: 16px;">
                                    <?php if (!empty($prod['sale_price']) && $prod['sale_price'] < $prod['base_price']): ?>
                                        <span><?= format_price((float)$prod['sale_price']) ?></span>
                                        <del style="font-size: 12px; color: #8C869B; margin-left: 6px;"><?= format_price((float)$prod['base_price']) ?></del>
                                    <?php else: ?>
                                        <span><?= format_price((float)$prod['base_price']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div>
                                <a href="<?= base_url('product/' . $prod['slug']) ?>" class="btn btn--brand btn--full" style="padding: 10px; font-size: 11px; letter-spacing: 0.1em; border-radius: 0; text-align: center;">
                                    SELECT SIZE & BUY &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    // Remove from Wishlist handler
    document.querySelectorAll('.btn-remove-wishlist').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const pId = this.dataset.productId;
            const card = document.getElementById('wishlist-item-' + pId);

            const formData = new FormData();
            formData.append('product_id', pId);
            formData.append('action', 'remove');

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

                    // Remove card with smooth transition
                    if (card) {
                        card.style.transition = 'opacity 0.2s, transform 0.2s';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            card.remove();
                            // If empty, reload to show empty state
                            if (data.count === 0) {
                                window.location.reload();
                            } else {
                                const counter = document.getElementById('wishlist-page-count');
                                if (counter) counter.textContent = data.count + (data.count === 1 ? ' SAVED ITEM' : ' SAVED ITEMS');
                            }
                        }, 250);
                    }
                }
            })
            .catch(err => console.error(err));
        });
    });
</script>
