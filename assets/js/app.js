/**
 * AMETHYSTCLOTHING.LK — FRONTEND APP JAVASCRIPT
 * Phase 3: Slide-Over Cart Engine, Dynamic Variant Selection, Single-Page Checkout & Order Tracking
 */

document.addEventListener('DOMContentLoaded', () => {
    const config = window.AMETHYST_CONFIG || { baseUrl: '', currency: 'LKR' };
    const baseUrl = config.baseUrl || '';

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
    // 3. SLIDE-OVER CART ENGINE (VANILLA JS & AJAX)
    // =========================================================================
    const cartTrigger = document.getElementById('cart-trigger');
    const cartDrawer = document.getElementById('cart-drawer');
    const cartOverlay = cartDrawer ? cartDrawer.querySelector('.cart-drawer__overlay') : null;
    const cartDrawerBody = document.getElementById('cart-drawer-body');
    const cartDrawerItems = document.getElementById('cart-drawer-items');
    const cartDrawerEmpty = document.getElementById('cart-drawer-empty');
    const cartDrawerFoot = document.getElementById('cart-drawer-foot');
    const cartHeaderCount = document.getElementById('cart-item-count');
    const cartHeaderSubtotal = document.getElementById('cart-subtotal');
    const cartDrawerCount = document.getElementById('cart-drawer-count');
    const cartDrawerSubtotal = document.getElementById('cart-drawer-subtotal');

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
    if (cartOverlay) cartOverlay.addEventListener('click', closeCart);

    document.querySelectorAll('.cart-drawer__close').forEach(btn => {
        btn.addEventListener('click', closeCart);
    });

    // Render Cart Drawer HTML from Cart Response Data
    const renderCartDrawer = (cartData) => {
        if (!cartData) return;

        const count = cartData.itemCount || 0;
        const formattedSubtotal = cartData.formattedSubtotal || 'Rs. 0.00';
        const items = cartData.items || [];

        // Update header triggers
        if (cartHeaderCount) cartHeaderCount.innerText = count;
        if (cartHeaderSubtotal) cartHeaderSubtotal.innerText = formattedSubtotal;
        if (cartDrawerCount) cartDrawerCount.innerText = count;
        if (cartDrawerSubtotal) cartDrawerSubtotal.innerText = formattedSubtotal;

        if (!cartDrawerItems) return;

        if (items.length === 0) {
            cartDrawerItems.innerHTML = '';
            if (cartDrawerEmpty) cartDrawerEmpty.style.display = 'block';
            if (cartDrawerFoot) cartDrawerFoot.style.display = 'none';
        } else {
            if (cartDrawerEmpty) cartDrawerEmpty.style.display = 'none';
            if (cartDrawerFoot) cartDrawerFoot.style.display = 'block';

            let itemsHtml = '';
            items.forEach(item => {
                itemsHtml += `
                    <div class="cart-item" data-variant-id="${item.variant_id}">
                        <img src="${item.image}" alt="${item.name}" class="cart-item__thumb">
                        <div class="cart-item__details">
                            <div>
                                <h4 class="cart-item__title">${item.name}</h4>
                                <div class="cart-item__meta">
                                    <span>SIZE: <strong>${item.size}</strong></span>
                                    <span>SKU: ${item.sku}</span>
                                </div>
                            </div>
                            <div class="cart-item__bottom">
                                <div class="cart-item__qty-control">
                                    <button type="button" class="cart-qty-btn cart-qty-btn--minus" data-variant-id="${item.variant_id}" data-current-qty="${item.quantity}">&minus;</button>
                                    <span class="cart-qty-val">${item.quantity}</span>
                                    <button type="button" class="cart-qty-btn cart-qty-btn--plus" data-variant-id="${item.variant_id}" data-current-qty="${item.quantity}" data-max-stock="${item.stock}">&#43;</button>
                                </div>
                                <span class="cart-item__price">${item.formatted_line_total}</span>
                            </div>
                        </div>
                        <button type="button" class="cart-item__remove" data-variant-id="${item.variant_id}" title="Remove item">&times;</button>
                    </div>
                `;
            });
            cartDrawerItems.innerHTML = itemsHtml;

            // Wire up quantity controls and remove buttons
            cartDrawerItems.querySelectorAll('.cart-qty-btn--minus').forEach(btn => {
                btn.addEventListener('click', () => {
                    const vId = btn.getAttribute('data-variant-id');
                    const curQty = parseInt(btn.getAttribute('data-current-qty'), 10);
                    updateCartItem(vId, curQty - 1);
                });
            });

            cartDrawerItems.querySelectorAll('.cart-qty-btn--plus').forEach(btn => {
                btn.addEventListener('click', () => {
                    const vId = btn.getAttribute('data-variant-id');
                    const curQty = parseInt(btn.getAttribute('data-current-qty'), 10);
                    const maxStock = parseInt(btn.getAttribute('data-max-stock'), 10);
                    if (curQty < maxStock) {
                        updateCartItem(vId, curQty + 1);
                    }
                });
            });

            cartDrawerItems.querySelectorAll('.cart-item__remove').forEach(btn => {
                btn.addEventListener('click', () => {
                    const vId = btn.getAttribute('data-variant-id');
                    removeCartItem(vId);
                });
            });
        }
    };

    // API: Fetch current cart on page load
    const fetchCartState = () => {
        fetch(baseUrl + '/ajax/cart.php?action=get')
            .then(res => res.json())
            .then(data => {
                if (data.success) renderCartDrawer(data);
            })
            .catch(err => console.error('Cart fetch error:', err));
    };

    // API: Add to cart
    const addToCart = (variantId, quantity = 1, onSuccess = null) => {
        fetch(baseUrl + '/ajax/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add', variant_id: variantId, quantity: quantity })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderCartDrawer(data);
                if (typeof onSuccess === 'function') onSuccess(data);
            } else {
                alert(data.message || 'Could not add item to bag.');
            }
        })
        .catch(err => console.error('Add to cart error:', err));
    };

    // API: Update quantity
    const updateCartItem = (variantId, quantity) => {
        fetch(baseUrl + '/ajax/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update', variant_id: variantId, quantity: quantity })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) renderCartDrawer(data);
        })
        .catch(err => console.error('Update cart error:', err));
    };

    // API: Remove item
    const removeCartItem = (variantId) => {
        fetch(baseUrl + '/ajax/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'remove', variant_id: variantId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) renderCartDrawer(data);
        })
        .catch(err => console.error('Remove cart item error:', err));
    };

    // Fetch initial cart state
    fetchCartState();

    // =========================================================================
    // 4. DYNAMIC VARIANT SELECTION & LIVE STOCK STATUS (PRODUCT DETAIL)
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

    let activeVariantId = null;
    let activeVariantStock = 0;

    const updateVariantState = (button) => {
        if (!button) return;

        sizeButtons.forEach(btn => btn.classList.remove('is-selected'));
        button.classList.add('is-selected');

        activeVariantId = parseInt(button.getAttribute('data-variant-id'), 10);
        const sku = button.getAttribute('data-sku') || '';
        const size = button.getAttribute('data-size') || '';
        const stock = parseInt(button.getAttribute('data-stock'), 10) || 0;
        activeVariantStock = stock;

        // 1. Update SKU Display & Barcode
        if (skuText) skuText.innerText = 'SKU: ' + sku;
        if (liveBarcode) liveBarcode.innerText = '*' + sku + '*';
        if (barcodeLabel) barcodeLabel.innerText = sku;
        if (selectedSizeLabel) selectedSizeLabel.innerText = 'SIZE: ' + size;

        // 2. Update Live Stock Status Badge & Order Buttons
        if (stockBadge) {
            stockBadge.className = 'stock-badge';

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

    // Add to Bag Button Click Handler
    if (addToBagBtn) {
        addToBagBtn.addEventListener('click', () => {
            if (activeVariantStock <= 0 || !activeVariantId) return;

            const originalText = addToBagBtn.innerText;
            addToBagBtn.innerText = 'ADDING...';

            addToCart(activeVariantId, 1, () => {
                addToBagBtn.innerText = 'ADDED TO BAG';
                addToBagBtn.style.backgroundColor = 'var(--status-success)';
                addToBagBtn.style.borderColor = 'var(--status-success)';

                setTimeout(() => {
                    openCart();
                    addToBagBtn.innerText = originalText;
                    addToBagBtn.style.backgroundColor = '';
                    addToBagBtn.style.borderColor = '';
                }, 400);
            });
        });
    }

    // Buy Now Button Click Handler (1-Click Guest Checkout)
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (activeVariantStock <= 0 || !activeVariantId) return;

            addToCart(activeVariantId, 1, () => {
                window.location.href = baseUrl + '/checkout';
            });
        });
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
    // 5. SINGLE-PAGE GUEST CHECKOUT LOGIC
    // =========================================================================
    const checkoutForm = document.getElementById('checkout-form');
    const districtSelect = document.getElementById('district');
    const summaryShipping = document.getElementById('summary-shipping');
    const summaryGrandTotal = document.getElementById('summary-grand-total');
    const summarySubtotalElem = document.getElementById('summary-subtotal');
    const shippingRateNote = document.getElementById('shipping-rate-note');
    const placeOrderBtn = document.getElementById('place-order-btn');
    const checkoutAlert = document.getElementById('checkout-alert');

    if (districtSelect && summarySubtotalElem) {
        const rawSubtotal = parseFloat(summarySubtotalElem.getAttribute('data-raw-subtotal')) || 0;
        const colomboSuburbs = ['Colombo', 'Gampaha', 'Kalutara'];
        const summaryDiscountElem = document.getElementById('summary-discount');
        const rowDiscount = document.getElementById('row-discount');
        const promoCodeInput = document.getElementById('promo-code-input');
        const applyPromoBtn = document.getElementById('apply-promo-btn');
        const removePromoBtn = document.getElementById('remove-promo-btn');
        const promoFeedback = document.getElementById('promo-feedback');
        const discountCodeDisplay = document.getElementById('discount-code-display');

        let currentDiscount = summaryDiscountElem ? (parseFloat(summaryDiscountElem.getAttribute('data-discount-amount')) || 0) : 0;

        const updateTotals = () => {
            const district = districtSelect.value;
            let fee = 450.00;
            let note = '(Outstation Delivery)';

            if (colomboSuburbs.includes(district)) {
                fee = 350.00;
                note = '(Colombo & Suburbs)';
            }

            if (rawSubtotal >= 7500.00) {
                fee = 0.00;
                note = '(Free Islandwide Delivery)';
            }

            const grand = Math.max(0, rawSubtotal - currentDiscount + fee);

            if (shippingRateNote) shippingRateNote.innerText = note;
            if (summaryShipping) {
                summaryShipping.innerText = fee === 0 ? 'FREE' : 'Rs. ' + fee.toFixed(2);
            }
            if (summaryGrandTotal) {
                summaryGrandTotal.innerText = 'Rs. ' + grand.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
        };

        districtSelect.addEventListener('change', updateTotals);
        updateTotals();

        // Apply Promo Code
        if (applyPromoBtn && promoCodeInput) {
            applyPromoBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const code = promoCodeInput.value.trim();
                if (!code) {
                    if (promoFeedback) {
                        promoFeedback.innerText = 'Please enter a promo code.';
                        promoFeedback.style.color = '#EF4444';
                        promoFeedback.style.display = 'block';
                    }
                    return;
                }

                applyPromoBtn.setAttribute('disabled', 'disabled');
                applyPromoBtn.innerText = '...';

                const formData = new FormData();
                formData.append('coupon_code', code);
                formData.append('action', 'apply');

                fetch(baseUrl + '/ajax/apply_coupon.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    applyPromoBtn.removeAttribute('disabled');
                    applyPromoBtn.innerText = 'APPLY';

                    if (data.success) {
                        currentDiscount = parseFloat(data.discount_amount) || 0;
                        if (summaryDiscountElem) {
                            summaryDiscountElem.innerText = '- ' + data.discount_formatted;
                            summaryDiscountElem.setAttribute('data-discount-amount', currentDiscount);
                        }
                        if (discountCodeDisplay) {
                            discountCodeDisplay.innerText = data.code;
                        }
                        if (rowDiscount) rowDiscount.style.display = '';
                        if (promoFeedback) {
                            promoFeedback.innerText = data.message;
                            promoFeedback.style.color = '#065F46';
                            promoFeedback.style.display = 'block';
                        }
                        updateTotals();
                        // Reload page after brief delay so server-side session syncs buttons cleanly
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        if (promoFeedback) {
                            promoFeedback.innerText = data.message || 'Invalid promo code.';
                            promoFeedback.style.color = '#EF4444';
                            promoFeedback.style.display = 'block';
                        }
                    }
                })
                .catch(err => {
                    applyPromoBtn.removeAttribute('disabled');
                    applyPromoBtn.innerText = 'APPLY';
                    if (promoFeedback) {
                        promoFeedback.innerText = 'Network error applying promo code.';
                        promoFeedback.style.color = '#EF4444';
                        promoFeedback.style.display = 'block';
                    }
                });
            });
        }

        // Remove Promo Code
        if (removePromoBtn) {
            removePromoBtn.addEventListener('click', (e) => {
                e.preventDefault();
                removePromoBtn.setAttribute('disabled', 'disabled');
                removePromoBtn.innerText = '...';

                const formData = new FormData();
                formData.append('action', 'remove');

                fetch(baseUrl + '/ajax/apply_coupon.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    window.location.reload();
                })
                .catch(err => {
                    window.location.reload();
                });
            });
        }
    }

    // Submit Checkout Form via AJAX
    if (placeOrderBtn && checkoutForm) {
        placeOrderBtn.addEventListener('click', (e) => {
            e.preventDefault();

            // Client-side Validation
            const fullName = document.getElementById('full_name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const address1 = document.getElementById('address_line1').value.trim();
            const city = document.getElementById('city').value.trim();
            const district = districtSelect ? districtSelect.value : '';

            if (!fullName || !phone || !address1 || !city || !district) {
                if (checkoutAlert) {
                    checkoutAlert.className = 'checkout-alert checkout-alert--error';
                    checkoutAlert.innerText = 'Please complete all required fields (Full Name, Phone, Address, City, District).';
                    checkoutAlert.style.display = 'block';
                }
                return;
            }

            placeOrderBtn.setAttribute('disabled', 'disabled');
            const originalBtnText = placeOrderBtn.innerText;
            placeOrderBtn.innerText = 'PROCESSING ORDER...';

            const formData = new FormData(checkoutForm);
            const payload = {};
            formData.forEach((value, key) => { payload[key] = value; });

            fetch(baseUrl + '/ajax/checkout_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.redirect) {
                    window.location.href = res.redirect;
                } else {
                    placeOrderBtn.removeAttribute('disabled');
                    placeOrderBtn.innerText = originalBtnText;
                    if (checkoutAlert) {
                        checkoutAlert.className = 'checkout-alert checkout-alert--error';
                        checkoutAlert.innerText = res.message || 'Failed to place order. Please check your information.';
                        checkoutAlert.style.display = 'block';
                    }
                }
            })
            .catch(err => {
                console.error('Checkout error:', err);
                placeOrderBtn.removeAttribute('disabled');
                placeOrderBtn.innerText = originalBtnText;
                if (checkoutAlert) {
                    checkoutAlert.className = 'checkout-alert checkout-alert--error';
                    checkoutAlert.innerText = 'A network error occurred. Please try again.';
                    checkoutAlert.style.display = 'block';
                }
            });
        });
    }

    // =========================================================================
    // 6. MOBILE NAVIGATION DRAWER
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
    // 7. MODALS & FLOATING ACTION HUB
    // =========================================================================
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
});
