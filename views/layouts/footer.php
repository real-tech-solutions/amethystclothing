<?php
/**
 * Footer & Floating Action Component — Amethystclothing.lk
 * High-Contrast Minimalist Streetwear (Cleaned & De-cluttered)
 */

$slogan = get_setting('slogan', 'Just own Your Style!');
$whatsappNumber = get_setting('whatsapp_number', '+94770000000');
$whatsappClean = preg_replace('/[^0-9]/', '', $whatsappNumber);
$contactEmail = get_setting('contact_email', 'hello@amethystclothing.lk');
?>
<footer class="site-footer">
    <!-- Main Footer Grid -->
    <div class="container site-footer__main">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-col footer-col--brand">
                <a href="<?= base_url() ?>" class="footer-logo">
                    <img src="<?= asset_url('images/logo_w-color.png') ?>" alt="AMETHYST" width="190" height="44">
                </a>
                <p class="footer-manifesto">
                    <?= e(__('footer_about')) ?>
                </p>
                <div class="footer-specialty-pill">
                    <span class="footer-specialty-pill__dot"></span>
                    <span>PREMIUM T-SHIRT PRINTING & CUSTOM APPAREL</span>
                </div>
            </div>

            <!-- Collections -->
            <div class="footer-col">
                <h4 class="footer-heading"><?= e(__('footer_categories')) ?></h4>
                <ul class="footer-links">
                    <li><a href="<?= base_url('shop') ?>"><?= e(__('nav_shop_all')) ?></a></li>
                    <li><a href="<?= base_url('category/baddie') ?>"><?= e(__('nav_baddie')) ?></a></li>
                    <li><a href="<?= base_url('category/oversized-comfort') ?>"><?= e(__('nav_oversized')) ?></a></li>
                    <li><a href="<?= base_url('category/luxury-mindset') ?>"><?= e(__('nav_luxury')) ?></a></li>
                    <li><a href="<?= base_url('category/couple-collections') ?>"><?= e(__('nav_couple')) ?></a></li>
                    <li><a href="<?= base_url('category/henley-neck-t-shirts') ?>"><?= e(__('nav_henley')) ?></a></li>
                </ul>
            </div>

            <!-- Customer Care -->
            <div class="footer-col">
                <h4 class="footer-heading"><?= e(__('footer_help')) ?></h4>
                <ul class="footer-links">
                    <li><a href="#" data-modal-target="track-modal"><?= e(__('track_order')) ?></a></li>
                    <li><a href="<?= base_url('shop') ?>">14-Day Size Exchange Policy</a></li>
                    <li><a href="<?= base_url('shop') ?>">Size & Measurement Guide</a></li>
                    <li><a href="<?= base_url('shop') ?>">Islandwide Shipping Rates</a></li>
                    <li><a href="<?= base_url('checkout') ?>">Fast Guest Checkout</a></li>
                    <li><a href="https://wa.me/<?= e($whatsappClean) ?>?text=<?= urlencode('Hi Amethyst! I need help with custom t-shirt printing.') ?>" target="_blank" rel="noopener">Custom Apparel Orders</a></li>
                </ul>
            </div>

            <!-- Clean Contact Us & Social Media -->
            <div class="footer-col footer-col--contact">
                <h4 class="footer-heading"><?= e(__('footer_contact')) ?></h4>
                <ul class="footer-contact-list">
                    <li>
                        <span class="contact-label">HOTLINE / WHATSAPP:</span>
                        <a href="https://wa.me/<?= e($whatsappClean) ?>" target="_blank" rel="noopener" class="contact-value highlight">+94 77 000 0000</a>
                    </li>
                    <li>
                        <span class="contact-label">EMAIL INQUIRIES:</span>
                        <a href="mailto:<?= e($contactEmail) ?>" class="contact-value"><?= e($contactEmail) ?></a>
                    </li>
                    <li>
                        <span class="contact-label">LOCATION:</span>
                        <span class="contact-value">Colombo, Sri Lanka</span>
                    </li>
                </ul>

                <h4 class="footer-heading" style="margin-top: 24px;"><?= e(__('footer_follow')) ?></h4>
                <div class="footer-socials">
                    <a href="https://instagram.com/amethystclothing.lk" target="_blank" rel="noopener" class="social-link" title="Instagram">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="2" width="20" height="20" rx="0" ry="0"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                        <span>INSTAGRAM</span>
                    </a>
                    <a href="https://facebook.com/amethystclothing.lk" target="_blank" rel="noopener" class="social-link" title="Facebook">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                        <span>FACEBOOK</span>
                    </a>
                    <a href="https://tiktok.com/@amethystclothing.lk" target="_blank" rel="noopener" class="social-link" title="TikTok">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
                        </svg>
                        <span>TIKTOK</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Payment Badges & Legal Bottom Row -->
        <div class="footer-bottom">
            <div class="footer-bottom__copy">
                <p>&copy; <?= date('Y') ?> AMETHYSTCLOTHING.LK. <?= e(__('footer_rights')) ?> "<?= e($slogan) ?>"</p>
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
        <a href="https://wa.me/<?= e($whatsappClean) ?>?text=<?= urlencode('Hi Amethyst! I want to order / need custom t-shirt printing assistance.') ?>" 
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
            <span><?= e(__('track_order')) ?></span>
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
