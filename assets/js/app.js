/**
 * AMETHYSTCLOTHING.LK — FRONTEND APP JAVASCRIPT
 * Phase 2: Hero Slider, Scroll-Snap Carousel, Dynamic Variant SKU & Live Stock Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    // =========================================================================
    // 1. HERO IMAGE SLIDER (AUTOPLAYING CROSSFADE)
    // =========================================================================
    const heroSlider = document.getElementById('hero-slider');
    if (heroSlider) {
        const slides = heroSlider.querySelectorAll('.hero-slide');
        const dots = heroSlider.querySelectorAll('.hero-dot');
        const prevBtn = document.getElementById('hero-slider-prev');
        const nextBtn = document.getElementById('hero-slider-next');
        let currentSlide = 0;
        let sliderTimer = null;
        const totalSlides = slides.length;

        const showSlide = (index) => {
            if (index < 0) index = totalSlides - 1;
            if (index >= totalSlides) index = 0;

            slides.forEach((slide, i) => {
                slide.classList.toggle('is-active', i === index);
            });

            dots.forEach((dot, i) => {
                dot.classList.toggle('is-active', i === index);
            });

            currentSlide = index;
        };

        const nextSlide = () => showSlide(currentSlide + 1);
        const prevSlide = () => showSlide(currentSlide - 1);

        const startAutoPlay = () => {
            if (sliderTimer) clearInterval(sliderTimer);
            sliderTimer = setInterval(nextSlide, 5000);
        };

        const stopAutoPlay = () => {
            if (sliderTimer) clearInterval(sliderTimer);
        };

        if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); startAutoPlay(); });
        if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); startAutoPlay(); });

        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                const targetSlide = parseInt(dot.getAttribute('data-slide'), 10);
                showSlide(targetSlide);
                startAutoPlay();
            });
        });

        heroSlider.addEventListener('mouseenter', stopAutoPlay);
        heroSlider.addEventListener('mouseleave', startAutoPlay);

        // Start autoplay on load
        startAutoPlay();
    }

    // =========================================================================
    // 2. PRODUCT CAROUSEL (HORIZONTAL SCROLL-SNAP BUTTONS)
    // =========================================================================
    const productCarousel = document.getElementById('product-carousel');
    const scrollPrevBtn = document.getElementById('product-scroll-prev');
    const scrollNextBtn = document.getElementById('product-scroll-next');

    if (productCarousel) {
        const scrollAmount = 340;

        if (scrollPrevBtn) {
            scrollPrevBtn.addEventListener('click', () => {
                productCarousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            });
        }

        if (scrollNextBtn) {
            scrollNextBtn.addEventListener('click', () => {
                productCarousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            });
        }
    }

    // =========================================================================
    // 3. DYNAMIC PRODUCT VARIANTS & LIVE STOCK STATUS (CRITICAL)
    // =========================================================================
    const sizeButtons = document.querySelectorAll('.size-btn');
    const skuText = document.getElementById('current-sku-text');
    const liveBarcode = document.getElementById('product-live-barcode');
    const barcodeLabel = document.getElementById('barcode-sku-label');
    const stockBadge = document.getElementById('stock-status-badge');
    const stockWarningBox = document.getElementById('stock-warning-box');
    const stockWarningText = document.getElementById('stock-warning-text');
    const selectedSizeLabel = document.getElementById('selected-size-label');
    const addToBagBtn = document.getElementById('add-to-bag-btn');
    const buyNowBtn = document.getElementById('buy-now-btn');

    const updateVariantState = (button) => {
        if (!button) return;

        // Deselect others, select current
        sizeButtons.forEach(btn => btn.classList.remove('is-selected'));
        button.classList.add('is-selected');

        const sku = button.getAttribute('data-sku') || '';
        const size = button.getAttribute('data-size') || '';
        const stock = parseInt(button.getAttribute('data-stock'), 10) || 0;

        // 1. Update SKU Display & Barcode
        if (skuText) skuText.innerText = 'SKU: ' + sku;
        if (liveBarcode) liveBarcode.innerText = '*' + sku + '*';
        if (barcodeLabel) barcodeLabel.innerText = sku;
        if (selectedSizeLabel) selectedSizeLabel.innerText = 'SIZE: ' + size;

        // 2. Update Live Stock Status Badge & Order Buttons
        if (stockBadge) {
            stockBadge.className = 'stock-badge'; // Reset classes

            if (stock > 5) {
                // In Stock (> 5)
                stockBadge.classList.add('stock-badge--in-stock');
                stockBadge.innerText = 'IN STOCK';
                if (stockWarningBox) stockWarningBox.style.display = 'none';

                if (addToBagBtn) {
                    addToBagBtn.removeAttribute('disabled');
                    addToBagBtn.classList.remove('is-disabled');
                    addToBagBtn.innerText = 'ADD TO BAG';
                }
                if (buyNowBtn) {
                    buyNowBtn.classList.remove('is-disabled');
                    buyNowBtn.style.pointerEvents = 'auto';
                }
            } else if (stock > 0 && stock <= 5) {
                // Low Stock (1 - 5)
                stockBadge.classList.add('stock-badge--low-stock');
                stockBadge.innerText = `LOW STOCK — ONLY ${stock} LEFT`;

                if (stockWarningBox && stockWarningText) {
                    stockWarningBox.style.display = 'flex';
                    stockWarningText.innerText = `Hurry! Only ${stock} items left in size ${size}. Order now to secure yours.`;
                }

                if (addToBagBtn) {
                    addToBagBtn.removeAttribute('disabled');
                    addToBagBtn.classList.remove('is-disabled');
                    addToBagBtn.innerText = 'ADD TO BAG';
                }
                if (buyNowBtn) {
                    buyNowBtn.classList.remove('is-disabled');
                    buyNowBtn.style.pointerEvents = 'auto';
                }
            } else {
                // Out of Stock (== 0)
                stockBadge.classList.add('stock-badge--out-of-stock');
                stockBadge.innerText = 'OUT OF STOCK';

                if (stockWarningBox && stockWarningText) {
                    stockWarningBox.style.display = 'flex';
                    stockWarningText.innerText = `Size ${size} is currently out of stock. Contact our WhatsApp concierge for restock priority.`;
                }

                if (addToBagBtn) {
                    addToBagBtn.setAttribute('disabled', 'disabled');
                    addToBagBtn.classList.add('is-disabled');
                    addToBagBtn.innerText = 'OUT OF STOCK';
                }
                if (buyNowBtn) {
                    buyNowBtn.classList.add('is-disabled');
                    buyNowBtn.style.pointerEvents = 'none';
                }
            }
        }
    };

    if (sizeButtons.length > 0) {
        sizeButtons.forEach(btn => {
            btn.addEventListener('click', () => updateVariantState(btn));
        });

        // Initialize with default selected button or first button
        const initialBtn = document.querySelector('.size-btn.is-selected') || sizeButtons[0];
        if (initialBtn) updateVariantState(initialBtn);
    }

    // Product Gallery Thumbnail Click Switcher
    const thumbButtons = document.querySelectorAll('.thumb-btn');
    const mainProductImage = document.getElementById('main-product-image');

    if (thumbButtons.length > 0 && mainProductImage) {
        thumbButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                thumbButtons.forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                const newSrc = btn.getAttribute('data-src');
                if (newSrc) {
                    mainProductImage.style.opacity = '0.4';
                    setTimeout(() => {
                        mainProductImage.src = newSrc;
                        mainProductImage.style.opacity = '1';
                    }, 120);
                }
            });
        });
    }

    // =========================================================================
    // 4. MOBILE NAVIGATION DRAWER
    // =========================================================================
    const navToggle = document.getElementById('nav-toggle');
    const mobileNavDrawer = document.getElementById('mobile-nav-drawer');
    const mobileNavClose = document.getElementById('mobile-nav-close');
    const mobileNavOverlay = document.getElementById('mobile-nav-overlay');

    const openMobileNav = () => {
        if (!mobileNavDrawer) return;
        mobileNavDrawer.classList.add('is-open');
        mobileNavDrawer.setAttribute('aria-hidden', 'false');
        if (navToggle) navToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    };

    const closeMobileNav = () => {
        if (!mobileNavDrawer) return;
        mobileNavDrawer.classList.remove('is-open');
        mobileNavDrawer.setAttribute('aria-hidden', 'true');
        if (navToggle) navToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    };

    if (navToggle) navToggle.addEventListener('click', openMobileNav);
    if (mobileNavClose) mobileNavClose.addEventListener('click', closeMobileNav);
    if (mobileNavOverlay) mobileNavOverlay.addEventListener('click', closeMobileNav);

    // =========================================================================
    // 5. SLIDE-OVER CART DRAWER & MODAL TRIGGERS
    // =========================================================================
    const cartTrigger = document.getElementById('cart-trigger');
    const cartDrawer = document.getElementById('cart-drawer');
    const cartClose = cartDrawer ? cartDrawer.querySelector('.cart-drawer__close') : null;
    const cartOverlay = cartDrawer ? cartDrawer.querySelector('.cart-drawer__overlay') : null;

    const openCart = () => {
        if (!cartDrawer) return;
        cartDrawer.classList.add('is-open');
        cartDrawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };

    const closeCart = () => {
        if (!cartDrawer) return;
        cartDrawer.classList.remove('is-open');
        cartDrawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    if (cartTrigger) cartTrigger.addEventListener('click', openCart);
    if (cartClose) cartClose.addEventListener('click', closeCart);
    if (cartOverlay) cartOverlay.addEventListener('click', closeCart);

    // Modals (e.g. Track Order Modal)
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    modalTriggers.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = btn.getAttribute('data-modal-target');
            const targetModal = document.getElementById(targetId);
            if (targetModal) {
                targetModal.classList.add('is-open');
                targetModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                const input = targetModal.querySelector('input');
                if (input) setTimeout(() => input.focus(), 100);
            }
        });
    });

    document.querySelectorAll('.modal').forEach(modal => {
        const closeBtn = modal.querySelector('.modal__close');
        const overlay = modal.querySelector('.modal__overlay');
        const closeModal = () => {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (overlay) overlay.addEventListener('click', closeModal);
    });

    // =========================================================================
    // 6. SEARCH DRAWER & FLOATING ACTION HUB
    // =========================================================================
    const searchToggleBtn = document.getElementById('search-toggle-btn');
    const searchDrawer = document.getElementById('search-drawer');

    if (searchToggleBtn && searchDrawer) {
        searchToggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            searchDrawer.classList.toggle('is-open');
            if (searchDrawer.classList.contains('is-open')) {
                const input = searchDrawer.querySelector('input');
                if (input) input.focus();
            }
        });

        document.addEventListener('click', (e) => {
            if (!searchDrawer.contains(e.target) && e.target !== searchToggleBtn) {
                searchDrawer.classList.remove('is-open');
            }
        });
    }

    const floatingHub = document.getElementById('floating-hub');
    const floatingTrigger = document.getElementById('floating-trigger');
    const scrollTopBtn = document.getElementById('scroll-top-btn');

    if (floatingTrigger && floatingHub) {
        floatingTrigger.addEventListener('click', () => {
            floatingHub.classList.toggle('is-active');
        });
    }

    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            if (floatingHub) floatingHub.classList.remove('is-active');
        });
    }

    // Keyboard ESC closes active modals/drawers
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCart();
            closeMobileNav();
            document.querySelectorAll('.modal.is-open').forEach(m => {
                m.classList.remove('is-open');
                m.setAttribute('aria-hidden', 'true');
            });
            if (searchDrawer) searchDrawer.classList.remove('is-open');
            if (floatingHub) floatingHub.classList.remove('is-active');
            document.body.style.overflow = '';
        }
    });

    // Add to Bag Button Animation & Feedback
    if (addToBagBtn) {
        addToBagBtn.addEventListener('click', () => {
            if (addToBagBtn.hasAttribute('disabled')) return;
            const originalText = addToBagBtn.innerText;
            addToBagBtn.innerText = 'ADDED TO BAG';
            addToBagBtn.style.backgroundColor = 'var(--status-success)';
            addToBagBtn.style.borderColor = 'var(--status-success)';
            
            const badge = document.getElementById('cart-item-count');
            if (badge) badge.innerText = '1';

            setTimeout(() => {
                openCart();
                addToBagBtn.innerText = originalText;
                addToBagBtn.style.backgroundColor = '';
                addToBagBtn.style.borderColor = '';
            }, 600);
        });
    }
});
