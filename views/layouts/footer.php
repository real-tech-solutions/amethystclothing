<?php
/**
 * Footer & Floating Action Component — Amethystclothing.lk
 * High-Contrast Streetwear / Dark Obsidian Grid
 */

$slogan = get_setting('slogan', 'Just own Your Style!');
$whatsappNumber = get_setting('whatsapp_number', '+94770000000');
$whatsappClean = preg_replace('/[^0-9]/', '', $whatsappNumber);
?>
<footer class="site-footer">
    <!-- Brand Tag Barcode Strip -->
    <div class="footer-barcode-strip">
        <div class="container footer-barcode-strip__inner">
            <span class="barcode-tag">*AMETHYST-LK-2026*</span>
            <span class="barcode-text">GENUINE STREETWEAR & LUXURY EDITIONS — CRAFTED IN SRI LANKA</span>
            <span class="barcode-tag">*000105-LK-PROD*</span>
        </div>
    </div>

    <!-- Main Footer Grid -->
    <div class="container site-footer__main">
        <div class="footer-grid">
            <!-- Brand Manifesto Column -->
            <div class="footer-col footer-col--brand">
                <a href="<?= base_url() ?>" class="footer-logo">
                    <img src="<?= asset_url('images/logo_w-color.png') ?>" alt="AMETHYST" width="200" height="46">
                </a>
                <p class="footer-manifesto">
                    Amethyst is an unapologetic streetwear & contemporary luxury label. Built with high-density combed cotton, razor-sharp silhouettes, and zero compromise. Just own your style.
                </p>
                <div class="footer-meta-pill">
                    <span class="footer-meta-pill__dot"></span>
                    <span>OFFICIAL ONLINE STORE • SRI LANKA</span>
                </div>
            </div>

            <!-- Categories -->
            <div class="footer-col">
                <h4 class="footer-heading">COLLECTIONS</h4>
                <ul class="footer-links">
                    <li><a href="<?= base_url('shop') ?>">ALL STREETWEAR</a></li>
                    <li><a href="<?= base_url('category/baddie') ?>">BADDIE / REBEL</a></li>
                    <li><a href="<?= base_url('category/oversized-comfort') ?>">220 GSM OVERSIZED</a></li>
                    <li><a href="<?= base_url('category/luxury-mindset') ?>">LUXURY MINDSET</a></li>
                    <li><a href="<?= base_url('category/couple-collections') ?>">COUPLE EDITIONS</a></li>
                    <li><a href="<?= base_url('category/henley-neck-t-shirts') ?>">CLASSIC HENLEYS</a></li>
                </ul>
            </div>

            <!-- Client Services -->
            <div class="footer-col">
                <h4 class="footer-heading">CLIENT SERVICES</h4>
                <ul class="footer-links">
                    <li><a href="#" data-modal-target="track-modal">TRACK LIVE ORDER</a></li>
                    <li><a href="<?= base_url('shop') ?>">14-DAY SIZE EXCHANGE</a></li>
                    <li><a href="<?= base_url('shop') ?>">SIZE & FIT GUIDE</a></li>
                    <li><a href="<?= base_url('shop') ?>">DELIVERY & SHIPPING</a></li>
                    <li><a href="<?= base_url('checkout') ?>">GUEST CHECKOUT</a></li>
                    <li><a href="https://wa.me/<?= e($whatsappClean) ?>?text=<?= urlencode('Hi Amethyst! I need support.') ?>" target="_blank" rel="noopener">WHATSAPP CONCIERGE</a></li>
                </ul>
            </div>

            <!-- Authentic Garment Tag Box -->
            <div class="footer-col footer-col--spec">
                <div class="garment-spec-box">
                    <div class="garment-spec-box__header">
                        <span class="garment-spec-box__title">SPEC // SPECIFICATION</span>
                        <span class="garment-spec-box__code">LK-2026</span>
                    </div>
                    <ul class="garment-spec-box__list">
                        <li><span>FABRIC:</span> <strong>100% COMBED COTTON</strong></li>
                        <li><span>WEIGHT:</span> <strong>180 – 220 GSM HEAVYWEIGHT</strong></li>
                        <li><span>ORIGIN:</span> <strong>COLOMBO, SRI LANKA</strong></li>
                        <li><span>CARE:</span> <strong>WASH COLD / DO NOT IRON ON PRINT</strong></li>
                    </ul>
                    <div class="garment-spec-box__barcode">
                        <span class="barcode-display">*AMETHYST-AUTHENTIC*</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Badges & Legal Bottom Row -->
        <div class="footer-bottom">
            <div class="footer-bottom__copy">
                <p>&copy; <?= date('Y') ?> AMETHYSTCLOTHING.LK. ALL RIGHTS RESERVED. "<?= e($slogan) ?>"</p>
            </div>
            <div class="footer-payments">
                <span class="payment-badge">CASH ON DELIVERY</span>
                <span class="payment-badge">VISA / MASTERCARD</span>
                <span class="payment-badge">KOKO PAY IN 3</span>
                <span class="payment-badge">MINTPAY</span>
            </div>
        </div>
    </div>
</footer>

<!-- Floating Quick Action Button (Bottom-Right) -->
<div class="floating-hub" id="floating-hub">
    <div class="floating-hub__menu" id="floating-menu" aria-hidden="true">
        <a href="https://wa.me/<?= e($whatsappClean) ?>?text=<?= urlencode('Hi Amethyst! I want to order / need assistance.') ?>" 
           target="_blank" rel="noopener" 
           class="floating-hub__action floating-hub__action--wa" 
           title="WhatsApp Concierge">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"></path>
            </svg>
            <span>WHATSAPP SUPPORT</span>
        </a>
        <button type="button" class="floating-hub__action" data-modal-target="track-modal" title="Track Live Order">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="3" width="15" height="13"></rect>
                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                <circle cx="18.5" cy="18.5" r="2.5"></circle>
            </svg>
            <span>TRACK ORDER</span>
        </button>
        <button type="button" class="floating-hub__action" id="scroll-top-btn" title="Scroll to Top">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="18 15 12 9 6 15"></polyline>
            </svg>
            <span>BACK TO TOP</span>
        </button>
    </div>
    <button type="button" class="floating-hub__trigger" id="floating-trigger" aria-label="Quick Actions">
        <span class="floating-hub__icon floating-hub__icon--default">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
        </span>
        <span class="floating-hub__icon floating-hub__icon--close">&times;</span>
    </button>
</div>
