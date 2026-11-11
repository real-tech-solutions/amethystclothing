<?php
/**
 * Header & Navigation Component — Amethystclothing.lk
 * Sharp Minimalist Luxury / High-Contrast Streetwear
 */

$announcementText = get_announcement_text();
$currentCurrency = current_currency();
$currencies = get_currencies();
?>
<!-- Top Utility / Announcement Bar -->
<aside class="announcement-bar" role="region" aria-label="Announcement">
    <div class="container announcement-bar__inner">
        <div class="announcement-bar__left">
            <span class="announcement-bar__slogan">JUST OWN YOUR STYLE!</span>
        </div>
        <div class="announcement-bar__center">
            <p class="announcement-bar__text"><?= e($announcementText) ?></p>
        </div>
        <div class="announcement-bar__right">
            <button type="button" class="announcement-bar__link" data-modal-target="track-modal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="1" y="3" width="15" height="13"></rect>
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                    <circle cx="5.5" cy="18.5" r="2.5"></circle>
                    <circle cx="18.5" cy="18.5" r="2.5"></circle>
                </svg>
                <span>TRACK ORDER</span>
            </button>
            <div class="currency-picker">
                <span class="currency-picker__label">CURRENCY:</span>
                <div class="currency-picker__switch">
                    <?php foreach ($currencies as $c): ?>
                        <a href="?currency=<?= e($c['code']) ?>" 
                           class="currency-picker__btn <?= $currentCurrency === $c['code'] ? 'is-active' : '' ?>">
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
            <img src="<?= asset_url('images/logo_b-color.png') ?>" alt="AMETHYST — Just own Your Style!" class="site-header__logo-img" width="180" height="42">
        </a>

        <!-- Main Desktop Navigation -->
        <nav class="site-nav" id="site-nav" aria-label="Main Navigation">
            <ul class="site-nav__list">
                <li class="site-nav__item">
                    <a href="<?= base_url('shop') ?>" class="site-nav__link">SHOP ALL</a>
                </li>
                <li class="site-nav__item">
                    <a href="<?= base_url('category/baddie') ?>" class="site-nav__link">BADDIE</a>
                </li>
                <li class="site-nav__item">
                    <a href="<?= base_url('category/oversized-comfort') ?>" class="site-nav__link">OVERSIZED</a>
                </li>
                <li class="site-nav__item">
                    <a href="<?= base_url('category/luxury-mindset') ?>" class="site-nav__link">LUXURY MINDSET</a>
                </li>
                <li class="site-nav__item">
                    <a href="<?= base_url('category/couple-collections') ?>" class="site-nav__link">COUPLE</a>
                </li>
                <li class="site-nav__item">
                    <a href="<?= base_url('category/henley-neck-t-shirts') ?>" class="site-nav__link">HENLEY</a>
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
                        <input type="text" name="q" class="header-search__input" placeholder="SEARCH STREETWEAR, OVERSIZED, HENLEY, SKUS..." autocomplete="off">
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
            <a href="<?= base_url('shop') ?>" class="action-btn" aria-label="Wishlist">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <span class="action-btn__badge">0</span>
            </a>

            <!-- Customer Account / Phone Auth -->
            <button type="button" class="action-btn" data-modal-target="track-modal" aria-label="Account">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </button>

            <!-- Cart Trigger Button -->
            <button type="button" class="cart-trigger" id="cart-trigger" aria-label="View shopping bag">
                <span class="cart-trigger__label">BAG</span>
                <span class="cart-trigger__count" id="cart-item-count">0</span>
                <span class="cart-trigger__total" id="cart-subtotal"><?= format_price(0) ?></span>
            </button>
        </div>
    </div>
</header>
