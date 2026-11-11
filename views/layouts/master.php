<?php
/**
 * Master Layout — Amethystclothing.lk
 * Variables available: $content, $pageTitle, $pageDescription
 */
$pageTitle       = $pageTitle ?? env('APP_NAME', 'Amethystclothing.lk');
$pageDescription = $pageDescription ?? env('APP_SLOGAN', 'Just own Your Style!');
?>
<!DOCTYPE html>
<html lang="en">
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
                <span class="cart-drawer__title">YOUR BAG</span>
                <button class="cart-drawer__close" aria-label="Close cart">&times;</button>
            </div>
            <div class="cart-drawer__body">
                <p class="cart-drawer__empty">Your bag is empty.</p>
            </div>
            <div class="cart-drawer__foot">
                <a href="<?= base_url('shop') ?>" class="btn btn--primary btn--full">CONTINUE SHOPPING</a>
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
                    <label for="track-input" class="track-form__label">ORDER NUMBER</label>
                    <input type="text" id="track-input" name="order" placeholder="AME-2026-XXXXXX" class="input" required>
                    <button type="submit" class="btn btn--primary btn--full">TRACK</button>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= asset_url('js/app.js') ?>"></script>
</body>
</html>
