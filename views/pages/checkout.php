<?php
/**
 * Single-Page 60-Second Guest Checkout — Amethystclothing.lk
 * Sharp Minimalist 2-Column Architectural Layout
 * Zero Friction, No Password Required, Dynamic District Shipping & Instant COD Dispatch
 */

$pageTitle = 'Guest Checkout — Amethystclothing.lk';

$cart = $_SESSION['cart'] ?? [];
$cartItems = array_values($cart);
$itemCount = 0;
$subtotal = 0.0;

foreach ($cartItems as $item) {
    $qty = (int)$item['quantity'];
    $price = (float)$item['price'];
    $itemCount += $qty;
    $subtotal += ($price * $qty);
}

// Sri Lanka Districts
$districts = [
    'Western Province' => ['Colombo', 'Gampaha', 'Kalutara'],
    'Central Province' => ['Kandy', 'Matale', 'Nuwara Eliya'],
    'Southern Province' => ['Galle', 'Matara', 'Hambantota'],
    'North Western Province' => ['Kurunegala', 'Puttalam'],
    'North Central Province' => ['Anuradhapura', 'Polonnaruwa'],
    'Uva Province' => ['Badulla', 'Monaragala'],
    'Sabaragamuwa Province' => ['Ratnapura', 'Kegalle'],
    'Eastern Province' => ['Trincomalee', 'Batticaloa', 'Ampara'],
    'Northern Province' => ['Jaffna', 'Kilinochchi', 'Mannar', 'Vavuniya', 'Mullaitivu']
];
?>
<section class="section checkout-section">
    <div class="container">
        <!-- Section Header -->
        <div class="section-head">
            <div class="section-head__left">
                <span class="section-eyebrow">FAST 60-SECOND GUEST CHECKOUT // NO PASSWORD REQUIRED</span>
                <h1 class="section-title">CHECKOUT</h1>
            </div>
            <div class="section-head__right">
                <span class="product-count-badge"><?= $itemCount ?> ITEMS IN BAG</span>
            </div>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="empty-checkout-box" style="text-align: center; padding: 80px 20px; border: 1px solid var(--border-crisp); background: #FFFFFF;">
                <h2 style="font-family: var(--font-display); font-size: 24px; text-transform: uppercase; margin-bottom: 12px;">YOUR SHOPPING BAG IS EMPTY</h2>
                <p style="font-family: var(--font-body); color: var(--text-secondary); margin-bottom: 24px;">Add items to your bag before proceeding to checkout.</p>
                <a href="<?= base_url('shop') ?>" class="btn btn--primary">EXPLORE STREETWEAR ARCHIVE</a>
            </div>
        <?php else: ?>
            <div class="checkout-layout">
                <!-- Left Column: Shipping & Customer Details Form -->
                <div class="checkout-form-column">
                    <form id="checkout-form" class="checkout-form" novalidate>
                        <!-- Form Section 1: Customer Contact -->
                        <div class="checkout-block">
                            <div class="checkout-block__header">
                                <span class="checkout-block__num">01 //</span>
                                <h3 class="checkout-block__title">CUSTOMER INFORMATION</h3>
                            </div>
                            <div class="form-grid">
                                <div class="form-group form-group--full">
                                    <label class="form-label" for="full_name">FULL NAME <span class="req">*</span></label>
                                    <input type="text" id="full_name" name="full_name" class="input" placeholder="e.g. Kasun Perera" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="phone">PRIMARY PHONE (FOR DELIVERY & OTP) <span class="req">*</span></label>
                                    <input type="tel" id="phone" name="phone" class="input" placeholder="077XXXXXXX or +9477XXXXXXX" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="secondary_phone">SECONDARY PHONE (OPTIONAL)</label>
                                    <input type="tel" id="secondary_phone" name="secondary_phone" class="input" placeholder="071XXXXXXX">
                                </div>
                                <div class="form-group form-group--full">
                                    <label class="form-label" for="email">EMAIL ADDRESS (FOR INVOICE COPY)</label>
                                    <input type="email" id="email" name="email" class="input" placeholder="kasun@example.com">
                                </div>
                            </div>
                        </div>

                        <!-- Form Section 2: Delivery Address -->
                        <div class="checkout-block">
                            <div class="checkout-block__header">
                                <span class="checkout-block__num">02 //</span>
                                <h3 class="checkout-block__title">DOORSTEP DELIVERY ADDRESS</h3>
                            </div>
                            <div class="form-grid">
                                <div class="form-group form-group--full">
                                    <label class="form-label" for="address_line1">STREET ADDRESS / HOUSE NUMBER <span class="req">*</span></label>
                                    <input type="text" id="address_line1" name="address_line1" class="input" placeholder="No. 124, Galle Road" required>
                                </div>
                                <div class="form-group form-group--full">
                                    <label class="form-label" for="address_line2">APARTMENT / SUITE / LANDMARK (OPTIONAL)</label>
                                    <input type="text" id="address_line2" name="address_line2" class="input" placeholder="Near Clock Tower">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="city">CITY / TOWN <span class="req">*</span></label>
                                    <input type="text" id="city" name="city" class="input" placeholder="e.g. Colombo 03, Kandy, Negombo" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="district">DISTRICT <span class="req">*</span></label>
                                    <select id="district" name="district" class="input select-input" required>
                                        <option value="">-- SELECT YOUR DISTRICT --</option>
                                        <?php foreach ($districts as $province => $provDistricts): ?>
                                            <optgroup label="<?= e($province) ?>">
                                                <?php foreach ($provDistricts as $d): ?>
                                                    <option value="<?= e($d) ?>" <?= $d === 'Colombo' ? 'selected' : '' ?>><?= e($d) ?></option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group form-group--full">
                                    <label class="form-label" for="delivery_notes">SPECIAL DELIVERY INSTRUCTIONS</label>
                                    <textarea id="delivery_notes" name="delivery_notes" class="input input--textarea" rows="2" placeholder="e.g. Call before delivery / Deliver after 3 PM"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Form Section 3: Payment Method -->
                        <div class="checkout-block">
                            <div class="checkout-block__header">
                                <span class="checkout-block__num">03 //</span>
                                <h3 class="checkout-block__title">PAYMENT METHOD</h3>
                            </div>
                            <div class="payment-selection-box">
                                <label class="payment-radio-card is-selected">
                                    <input type="radio" name="payment_method" value="cod" checked>
                                    <div class="payment-radio-card__body">
                                        <div class="payment-radio-card__top">
                                            <strong>CASH ON DELIVERY (COD)</strong>
                                            <span class="badge badge--success">RECOMMENDED</span>
                                        </div>
                                        <p class="payment-radio-card__desc">
                                            Pay cash in Sri Lankan Rupees directly to the courier partner upon doorstep package inspection.
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Submission Alert Message Container -->
                        <div id="checkout-alert" class="checkout-alert" style="display: none;"></div>
                    </form>
                </div>

                <!-- Right Column: Sticky Order Summary -->
                <div class="checkout-summary-column">
                    <div class="checkout-summary-card">
                        <div class="checkout-summary-card__header">
                            <h3 class="checkout-summary-card__title">ORDER SUMMARY</h3>
                            <span class="summary-item-count"><?= $itemCount ?> ITEMS</span>
                        </div>

                        <!-- Item Breakdown List -->
                        <div class="checkout-items-list">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="checkout-item">
                                    <img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" class="checkout-item__img">
                                    <div class="checkout-item__info">
                                        <h4 class="checkout-item__title"><?= e($item['name']) ?></h4>
                                        <div class="checkout-item__meta">
                                            <span>SKU: <?= e($item['sku']) ?></span>
                                            <span>SIZE: <strong><?= e($item['size']) ?></strong></span>
                                            <span>QTY: <strong><?= (int)$item['quantity'] ?></strong></span>
                                        </div>
                                        <div class="checkout-item__price">
                                            <?= format_price((float)$item['price'] * (int)$item['quantity']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Financial Totals Table -->
                        <div class="checkout-totals">
                            <div class="checkout-totals__row">
                                <span>SUBTOTAL:</span>
                                <strong id="summary-subtotal" data-raw-subtotal="<?= $subtotal ?>">
                                    <?= format_price($subtotal) ?>
                                </strong>
                            </div>
                            <div class="checkout-totals__row">
                                <div class="shipping-label-group">
                                    <span>SHIPPING:</span>
                                    <small id="shipping-rate-note">(Colombo & Suburbs)</small>
                                </div>
                                <strong id="summary-shipping">Rs. 350.00</strong>
                            </div>
                            <div class="checkout-totals__row checkout-totals__row--grand">
                                <span>TOTAL TO PAY:</span>
                                <strong id="summary-grand-total">
                                    <?= format_price($subtotal + 350.00) ?>
                                </strong>
                            </div>
                        </div>

                        <!-- Guarantee Points -->
                        <div class="checkout-guarantee-strip">
                            <div class="guarantee-point">
                                <span class="guarantee-point__dot"></span>
                                <span>14-Day Easy Size Exchange Policy</span>
                            </div>
                            <div class="guarantee-point">
                                <span class="guarantee-point__dot"></span>
                                <span>Cash on Delivery Inspection Guaranteed</span>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <button type="button" id="place-order-btn" class="btn btn--brand btn--full btn--lg">
                            PLACE ORDER (CASH ON DELIVERY)
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
