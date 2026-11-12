<?php
/**
 * Master Layout — Amethystclothing.lk
 * Variables available: $content, $pageTitle, $pageDescription
 */
$pageTitle       = $pageTitle ?? env('APP_NAME', 'Amethystclothing.lk');
$pageDescription = $pageDescription ?? env('APP_SLOGAN', 'Just own Your Style!');
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang() ?? 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <link rel="icon" type="image/png" href="<?= asset_url('images/icon_b.png') ?>">

    <!-- Google Fonts: Archivo Black | Archivo Narrow | Archivo | Libre Barcode 39 Text | Noto Sans Sinhala -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Archivo+Narrow:ital,wght@0,400..700;1,400..700&family=Archivo:ital,wght@0,100..900;1,100..900&family=Libre+Barcode+39+Text&family=Noto+Sans+Sinhala:wght@100..900&display=swap" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= asset_url('css/main.css') ?>">
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>

    <main id="main-content">
        <?= $content ?>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>

    <!-- Cart Drawer -->
    <div id="cart-drawer" class="cart-drawer" aria-hidden="true">
        <div class="cart-drawer__overlay"></div>
        <div class="cart-drawer__panel">
            <div class="cart-drawer__head">
                <span class="cart-drawer__title">YOUR BAG (<span id="cart-drawer-count">0</span>)</span>
                <button class="cart-drawer__close" aria-label="Close cart">&times;</button>
            </div>
            <div class="cart-drawer__body" id="cart-drawer-body">
                <div id="cart-drawer-items" class="cart-drawer__items"></div>
                <div id="cart-drawer-empty" class="cart-drawer__empty">
                    <p>Your shopping bag is currently empty.</p>
                    <a href="<?= base_url('shop') ?>" class="btn btn--outline btn--sm" style="margin-top: 16px;">EXPLORE STREETWEAR</a>
                </div>
            </div>
            <div class="cart-drawer__foot" id="cart-drawer-foot" style="display: none;">
                <div class="cart-drawer__subtotal-row">
                    <span class="cart-drawer__subtotal-label">SUBTOTAL:</span>
                    <strong class="cart-drawer__subtotal-value" id="cart-drawer-subtotal">Rs. 0.00</strong>
                </div>
                <p class="cart-drawer__shipping-note">Taxes & shipping calculated at checkout</p>
                <a href="<?= base_url('checkout') ?>" class="btn btn--brand btn--full" id="cart-drawer-checkout-btn">
                    <?= is_customer_logged_in() ? 'PROCEED TO CHECKOUT' : 'PROCEED TO GUEST CHECKOUT' ?>
                </a>
                <button type="button" class="btn btn--outline btn--full cart-drawer__close" style="margin-top: 8px;">CONTINUE SHOPPING</button>
            </div>
        </div>
    </div>

    <!-- Track Order Modal -->
    <div id="track-modal" class="modal" aria-hidden="true">
        <div class="modal__overlay"></div>
        <div class="modal__panel">
            <div class="modal__head">
                <span class="modal__title">TRACK YOUR ORDER</span>
                <button class="modal__close" aria-label="Close">&times;</button>
            </div>
            <div class="modal__body">
                <form action="<?= base_url('track-order') ?>" method="GET" class="track-form">
                    <label for="track-input" class="track-form__label">ORDER NUMBER OR PHONE</label>
                    <input type="text" id="track-input" name="order" placeholder="e.g. AME-2026-000101 or 077XXXXXXX" class="input" required>
                    <button type="submit" class="btn btn--primary btn--full">TRACK SHIPMENT</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Global App Configuration -->
    <script>
        window.AMETHYST_CONFIG = {
            baseUrl: <?= json_encode(base_url()) ?>,
            currency: <?= json_encode(current_currency()) ?>
        };
    </script>
    <script src="<?= asset_url('js/app.js') ?>"></script>
</body>
</html>
