/**
 * AMETHYSTCLOTHING.LK — JAVASCRIPT APP CORE
 * Phase 1: Micro-interactions, Modals, Slide-Over Cart Drawer, Floating Hub
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Slide-Over Cart Drawer
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

    // 2. Modals (e.g. Track Order Modal)
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

    // 3. Header Search Toggle
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

    // 4. Floating Hub Toggle & Scroll to Top
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

    // 5. Size Selector Buttons on Detail Page
    const sizeButtons = document.querySelectorAll('.size-btn');
    sizeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            sizeButtons.forEach(b => b.classList.remove('is-selected'));
            btn.classList.add('is-selected');
        });
    });

    // 6. Keyboard Accessibility (Escape key closes modals/drawers)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCart();
            document.querySelectorAll('.modal.is-open').forEach(m => {
                m.classList.remove('is-open');
                m.setAttribute('aria-hidden', 'true');
            });
            if (searchDrawer) searchDrawer.classList.remove('is-open');
            if (floatingHub) floatingHub.classList.remove('is-active');
            document.body.style.overflow = '';
        }
    });

    // 7. Add to Bag Demo Feedback
    const addToBagBtn = document.getElementById('add-to-bag-btn');
    if (addToBagBtn) {
        addToBagBtn.addEventListener('click', () => {
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
            }, 500);
        });
    }
});
