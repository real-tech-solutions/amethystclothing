<?php
/**
 * Track Order Page — Amethystclothing.lk
 */
$pageTitle = 'Track Live Order — Amethystclothing.lk';
$orderParam = trim($_GET['order'] ?? '');
?>
<section class="section">
    <div class="container" style="max-width: 640px;">
        <div class="section-head section-head--center">
            <span class="section-eyebrow">LIVE COURIER & PRODUCTION DISPATCH</span>
            <h1 class="section-title">TRACK YOUR ORDER</h1>
            <p class="section-subtitle">Enter your AME order number or phone number to view real-time production and courier tracking.</p>
        </div>

        <div class="track-card">
            <form action="<?= base_url('track-order') ?>" method="GET" class="track-form">
                <div class="form-group">
                    <label class="form-label" for="order-num">ORDER NUMBER OR PHONE</label>
                    <input type="text" id="order-num" name="order" value="<?= e($orderParam) ?>" placeholder="e.g. AME-2026-000105 or 077XXXXXXX" class="input input--lg" required>
                </div>
                <button type="submit" class="btn btn--primary btn--full" style="margin-top: 16px;">
                    CHECK STATUS
                </button>
            </form>

            <?php if (!empty($orderParam)): ?>
                <div class="track-result-box" style="margin-top: 32px; border: 1px solid var(--border-crisp); padding: 24px; background: var(--bg-secondary);">
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid var(--border-crisp); padding-bottom: 12px; margin-bottom: 16px;">
                        <span style="font-family: var(--font-condensed); font-weight:700; letter-spacing:0.08em;">LOOKUP: <?= e($orderParam) ?></span>
                        <span class="barcode-tag">*<?= e($orderParam) ?>*</span>
                    </div>
                    <p style="font-family: var(--font-body); font-size: 14px; color: var(--text-secondary);">
                        Order status engine will connect to live courier partner APIs in Phase 4. For immediate order updates, contact our WhatsApp Concierge.
                    </p>
                    <a href="https://wa.me/94770000000?text=<?= urlencode('Checking status for ' . $orderParam) ?>" target="_blank" rel="noopener" class="btn btn--outline btn--full" style="margin-top: 16px;">
                        WHATSAPP DISPATCH HELPLINE
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
