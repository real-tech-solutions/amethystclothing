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
            </div>

            <!-- Collections -->
            <div class="footer-col">
                <h4 class="footer-heading"><?= e(__('footer_categories')) ?></h4>
                <ul class="footer-links">
                    <li><a href="<?= base_url('shop') ?>"><?= e(__('nav_shop_all')) ?></a></li>
                    <li><a href="<?= base_url('categories') ?>"><?= e(__('nav_categories')) ?> (ALL)</a></li>
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
                    <li><a href="<?= base_url('support') ?>#exchange">14-Day Doorstep Size Exchange</a></li>
                    <li><a href="<?= base_url('support') ?>#size-guide">Size & Measurement Guides</a></li>
                    <li><a href="<?= base_url('support') ?>#shipping">Islandwide Delivery Rates</a></li>
                    <li><a href="<?= base_url('about') ?>"><?= e(__('nav_about')) ?></a></li>
                    <li><a href="<?= base_url('contact') ?>"><?= e(__('nav_contact')) ?></a></li>
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
                </ul>

                <h4 class="footer-heading" style="margin-top: 24px;"><?= e(__('footer_follow')) ?></h4>
                <div class="footer-socials">
                    <a href="https://instagram.com/amethystclothing.lk" target="_blank" rel="noopener" class="social-link" title="Instagram">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="2" width="20" height="20"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                        <span>INSTAGRAM</span>
                    </a>
                    <a href="https://facebook.com/amethystclothing.lk" target="_blank" rel="noopener" class="social-link" title="Facebook">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                        <span>FACEBOOK</span>
                    </a>
                    <a href="https://tiktok.com/@amethystclothing.lk" target="_blank" rel="noopener" class="social-link" title="TikTok">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
                        </svg>
                        <span>TIKTOK</span>
                    </a>
                    <a href="https://youtube.com/@amethystclothinglk" target="_blank" rel="noopener" class="social-link" title="YouTube">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                            <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                        </svg>
                        <span>YOUTUBE</span>
                    </a>
                    <a href="https://discord.gg/amethystclothing" target="_blank" rel="noopener" class="social-link" title="Discord">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6h0a14.5 14.5 0 0 0-4-1.2 10.3 10.3 0 0 0-.5 1 13.6 13.6 0 0 0-3 0 10.3 10.3 0 0 0-.5-1A14.5 14.5 0 0 0 6 6a15.8 15.8 0 0 0-2.8 11.2 14.8 14.8 0 0 0 4.6 2.3c.4-.5.7-1.1 1-1.7a9.7 9.7 0 0 1-1.6-.8c.1-.1.3-.2.4-.3 3.1 1.4 6.5 1.4 9.6 0 .1.1.3.2.4.3a9.7 9.7 0 0 1-1.6.8c.3.6.6 1.2 1 1.7a14.8 14.8 0 0 0 4.6-2.3A15.8 15.8 0 0 0 18 6z"></path>
                            <circle cx="9.5" cy="12" r="1"></circle>
                            <circle cx="14.5" cy="12" r="1"></circle>
                        </svg>
                        <span>DISCORD</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Payment Badges & Legal Bottom Row -->
        <div class="footer-bottom">
            <div class="footer-bottom__copy">
                <p>&copy; 2026 AMETHYSTCLOTHING.LK. ALL RIGHTS RESERVED. Crafted By <a href="https://realtechsolutions.com.lk" target="_blank" rel="noopener" style="color:var(--brand-light);text-decoration:underline;">RealTechSolutions</a></p>
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
