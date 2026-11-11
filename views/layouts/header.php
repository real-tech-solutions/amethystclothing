<?php
/**
 * Header & Navigation Component — Amethystclothing.lk
 * Sharp Minimalist Luxury / High-Contrast Streetwear
 * Bilingual Support: EN | සිං + Multi-Currency LKR | USD
 */

$currentLang = current_lang();
$currentCurrency = current_currency();
$currencies = get_currencies();
?>
<!-- Top Utility / Announcement Bar -->
<aside class="announcement-bar" role="region" aria-label="Announcement">
    <div class="container announcement-bar__inner">
        <div class="announcement-bar__left">
            <span class="announcement-bar__slogan"><?= e(__('slogan')) ?></span>
        </div>
        <div class="announcement-bar__center">
            <p class="announcement-bar__text"><?= e(__('announcement')) ?></p>
        </div>
        <div class="announcement-bar__right">
            <!-- Order Tracking Trigger -->
            <button type="button" class="announcement-bar__link" data-modal-target="track-modal">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
                <span><?= e(__('track_order')) ?></span>
            </button>

            <!-- Language Switcher EN | සිං -->
            <div class="top-switch-group">
                <span class="top-switch-group__label"><?= e(__('language')) ?>:</span>
                <div class="top-switch">
                    <a href="?lang=en" class="top-switch__btn <?= $currentLang === 'en' ? 'is-active' : '' ?>">EN</a>
                    <a href="?lang=si" class="top-switch__btn <?= $currentLang === 'si' ? 'is-active' : '' ?>">සිං</a>
                </div>
            </div>

            <!-- Currency Switcher LKR | USD -->
            <div class="top-switch-group">
                <span class="top-switch-group__label"><?= e(__('currency')) ?>:</span>
                <div class="top-switch">
                    <?php foreach ($currencies as $c): ?>
                        <a href="?currency=<?= e($c['code']) ?>" 
                           class="top-switch__btn <?= $currentCurrency === $c['code'] ? 'is-active' : '' ?>">
                            <?= e($c['code']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Main Architectural Sticky Header -->
<header class="site-header" id="site-header">
    <div class="container site-header__inner">
        <!-- Mobile Hamburger Trigger -->
        <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
            <span class="nav-toggle__bar"></span>
            <span class="nav-toggle__bar"></span>
            <span class="nav-toggle__bar"></span>
        </button>

        <!-- Brand Logo -->
        <a href="<?= base_url() ?>" class="site-header__logo" aria-label="Amethyst Home">
            <img src="<?= asset_url('images/logo_b-color.png') ?>" alt="AMETHYST — <?= e(__('slogan')) ?>" class="site-header__logo-img" width="180" height="42">
        </a>

        <!-- Main Desktop Navigation -->
        <nav class="site-nav" id="site-nav" aria-label="Main Navigation">
            <ul class="site-nav__list">
                <li class="site-nav__item">
                    <a href="<?= base_url('shop') ?>" class="site-nav__link"><?= e(__('nav_shop_all')) ?></a>
                </li>
                <li class="site-nav__item">
                    <a href="<?= base_url('category/baddie') ?>" class="site-nav__link"><?= e(__('nav_baddie')) ?></a>
                </li>
                <li class="site-nav__item">
                    <a href="<?= base_url('category/oversized-comfort') ?>" class="site-nav__link"><?= e(__('nav_oversized')) ?></a>
                </li>
                <li class="site-nav__item">
                    <a href="<?= base_url('category/luxury-mindset') ?>" class="site-nav__link"><?= e(__('nav_luxury')) ?></a>
                </li>
                <li class="site-nav__item">
                    <a href="<?= base_url('category/couple-collections') ?>" class="site-nav__link"><?= e(__('nav_couple')) ?></a>
                </li>
                <li class="site-nav__item">
                    <a href="<?= base_url('category/henley-neck-t-shirts') ?>" class="site-nav__link"><?= e(__('nav_henley')) ?></a>
                </li>
            </ul>
        </nav>

        <!-- Header Actions -->
        <div class="site-header__actions">
            <!-- Predictive Search Trigger -->
            <div class="header-search" id="header-search">
                <button type="button" class="action-btn" id="search-toggle-btn" aria-label="Open search">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
                <div class="header-search__drawer" id="search-drawer" aria-hidden="true">
                    <form action="<?= base_url('shop') ?>" method="GET" class="header-search__form">
                        <input type="text" name="q" class="header-search__input" placeholder="<?= e(__('search_placeholder')) ?>" autocomplete="off">
                        <button type="submit" class="header-search__submit" aria-label="Search">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Wishlist Trigger -->
            <a href="<?= base_url('wishlist') ?>" class="action-btn" aria-label="Wishlist" title="View Wishlist">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <span class="action-btn__badge" id="wishlist-badge"><?= count($_SESSION['wishlist'] ?? []) ?></span>
            </a>

            <!-- Customer Account Trigger -->
            <?php if (is_customer_logged_in()): ?>
                <?php $loggedInCustomer = customer_user(); ?>
                <a href="<?= base_url('account/dashboard') ?>" class="action-btn" aria-label="My Account" title="My Account (<?= e($loggedInCustomer['full_name'] ?: $loggedInCustomer['phone']) ?>)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span style="position:absolute;top:6px;right:6px;width:7px;height:7px;background:var(--brand-deep);border-radius:50%;"></span>
                </a>
            <?php else: ?>
                <a href="<?= base_url('auth') ?>" class="action-btn" aria-label="Sign In / Register" title="Sign In / Register">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>
            <?php endif; ?>

            <!-- Cart Trigger Button -->
            <button type="button" class="cart-trigger" id="cart-trigger" aria-label="View shopping bag">
                <span class="cart-trigger__label"><?= e(__('bag')) ?></span>
                <span class="cart-trigger__count" id="cart-item-count">0</span>
                <span class="cart-trigger__total" id="cart-subtotal"><?= format_price(0) ?></span>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Navigation Drawer -->
<div class="mobile-nav-drawer" id="mobile-nav-drawer" aria-hidden="true">
    <div class="mobile-nav-drawer__overlay" id="mobile-nav-overlay"></div>
    <div class="mobile-nav-drawer__panel">
        <div class="mobile-nav-drawer__head">
            <img src="<?= asset_url('images/logo_b-color.png') ?>" alt="AMETHYST" width="140" height="32">
            <button class="mobile-nav-drawer__close" id="mobile-nav-close" aria-label="Close menu">&times;</button>
        </div>
        <ul class="mobile-nav-drawer__list">
            <li><a href="<?= base_url('shop') ?>" class="mobile-nav-drawer__link"><?= e(__('nav_shop_all')) ?></a></li>
            <li><a href="<?= base_url('category/baddie') ?>" class="mobile-nav-drawer__link"><?= e(__('nav_baddie')) ?></a></li>
            <li><a href="<?= base_url('category/oversized-comfort') ?>" class="mobile-nav-drawer__link"><?= e(__('nav_oversized')) ?></a></li>
            <li><a href="<?= base_url('category/luxury-mindset') ?>" class="mobile-nav-drawer__link"><?= e(__('nav_luxury')) ?></a></li>
            <li><a href="<?= base_url('category/couple-collections') ?>" class="mobile-nav-drawer__link"><?= e(__('nav_couple')) ?></a></li>
            <li><a href="<?= base_url('category/henley-neck-t-shirts') ?>" class="mobile-nav-drawer__link"><?= e(__('nav_henley')) ?></a></li>
            <li><a href="<?= base_url('track-order') ?>" class="mobile-nav-drawer__link"><?= e(__('track_order')) ?></a></li>
        </ul>
        <div class="mobile-nav-drawer__foot">
            <div class="mobile-nav-switch">
                <span><?= e(__('language')) ?>:</span>
                <a href="?lang=en" class="<?= $currentLang === 'en' ? 'is-active' : '' ?>">EN</a>
                <a href="?lang=si" class="<?= $currentLang === 'si' ? 'is-active' : '' ?>">සිං</a>
            </div>
            <div class="mobile-nav-switch">
                <span><?= e(__('currency')) ?>:</span>
                <a href="?currency=LKR" class="<?= $currentCurrency === 'LKR' ? 'is-active' : '' ?>">LKR</a>
                <a href="?currency=USD" class="<?= $currentCurrency === 'USD' ? 'is-active' : '' ?>">USD</a>
            </div>
        </div>
    </div>
</div>
