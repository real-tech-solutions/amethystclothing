<?php
/**
 * Amethystclothing.lk — Customer Account Dashboard
 * Sharp Minimalist Luxury UI — Strictly No AI Slop
 */

require_once __DIR__ . '/../../../core/helpers.php';
require_customer_auth();

$customer = customer_user();
$pageTitle = 'My Account // AMETHYST';
$tab = trim($_GET['tab'] ?? 'profile');

// Fetch Customer's Orders
$customerOrders = [];
try {
    $stmt = db()->prepare("
        SELECT o.*, 
               (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as total_items
        FROM orders o
        WHERE o.customer_id = ? OR o.customer_phone = ?
        ORDER BY o.ordered_at DESC
    ");
    $stmt->execute([(int)$customer['id'], $customer['phone']]);
    $customerOrders = $stmt->fetchAll();
} catch (Exception $e) {}

// Fetch Saved Addresses
$addresses = [];
try {
    $stmt = db()->prepare("SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC, id DESC");
    $stmt->execute([(int)$customer['id']]);
    $addresses = $stmt->fetchAll();
} catch (Exception $e) {}

// Sri Lanka Districts for address modal
$districts = ['Colombo', 'Gampaha', 'Kalutara', 'Kandy', 'Galle', 'Matara', 'Kurunegala', 'Ratnapura', 'Anuradhapura', 'Jaffna', 'Badulla', 'Trincomalee'];
?>

<section class="section account-section" style="padding: 60px 0 100px;">
    <div class="container">
        <!-- Section Breadcrumb -->
        <div style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border-crisp); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <span style="font-family: var(--font-mono); font-size: 11px; letter-spacing: 0.15em; color: var(--brand-deep); text-transform: uppercase;">
                    MEMBER DASHBOARD // PERSONAL ARCHIVE
                </span>
                <h1 style="font-size: 26px; font-weight: 800; text-transform: uppercase; margin: 4px 0 0; letter-spacing: -0.01em;">
                    MY ACCOUNT
                </h1>
            </div>
            <a href="<?= base_url('account/logout') ?>" class="btn btn--outline btn--sm" style="font-size: 11px; border-radius: 0; color: #EF4444; border-color: #FECACA;">
                LOGOUT
            </a>
        </div>

        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 40px; align-items: start;">
            <!-- Left Sidebar Navigation -->
            <aside style="background: #FFFFFF; border: 1px solid var(--border-crisp);">
                <!-- User Profile Badge -->
                <div style="padding: 24px 20px; background: #0E0C14; color: #FFFFFF; border-bottom: 3px solid var(--brand-deep);">
                    <div style="font-size: 16px; font-weight: 800; text-transform: uppercase; margin-bottom: 2px;">
                        <?= e($customer['full_name'] ?: 'Customer') ?>
                    </div>
                    <div style="font-family: var(--font-mono); font-size: 12px; color: #9966cc; margin-bottom: 10px;">
                        <?= e($customer['phone']) ?>
                    </div>
                    <span style="font-family: var(--font-mono); font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; background: rgba(153, 102, 204, 0.2); color: #B5AFC2; padding: 2px 8px; border: 1px solid rgba(153, 102, 204, 0.4);">
                        TIER: <?= strtoupper(e($customer['loyalty_tier'] ?: 'Standard')) ?>
                    </span>
                </div>

                <!-- Nav List -->
                <ul style="list-style: none; margin: 0; padding: 0;">
                    <li>
                        <a href="<?= base_url('account/dashboard?tab=profile') ?>" 
                           style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; text-decoration: none; font-size: 13px; font-weight: 600; color: <?= $tab === 'profile' ? 'var(--brand-deep)' : 'var(--text-primary)' ?>; background: <?= $tab === 'profile' ? 'rgba(78,42,132,0.06)' : 'transparent' ?>; border-left: 3px solid <?= $tab === 'profile' ? 'var(--brand-deep)' : 'transparent' ?>; border-bottom: 1px solid #F0EEF5;">
                            <span>01 // My Profile & Loyalty</span>
                            <span style="font-family: var(--font-mono); font-size: 11px;">&rarr;</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('account/dashboard?tab=orders') ?>" 
                           style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; text-decoration: none; font-size: 13px; font-weight: 600; color: <?= $tab === 'orders' ? 'var(--brand-deep)' : 'var(--text-primary)' ?>; background: <?= $tab === 'orders' ? 'rgba(78,42,132,0.06)' : 'transparent' ?>; border-left: 3px solid <?= $tab === 'orders' ? 'var(--brand-deep)' : 'transparent' ?>; border-bottom: 1px solid #F0EEF5;">
                            <span>02 // Order History</span>
                            <span style="font-family: var(--font-mono); font-size: 11px; background: var(--bg-dark); color: #FFF; padding: 1px 6px;">
                                <?= count($customerOrders) ?>
                            </span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('account/dashboard?tab=addresses') ?>" 
                           style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; text-decoration: none; font-size: 13px; font-weight: 600; color: <?= $tab === 'addresses' ? 'var(--brand-deep)' : 'var(--text-primary)' ?>; background: <?= $tab === 'addresses' ? 'rgba(78,42,132,0.06)' : 'transparent' ?>; border-left: 3px solid <?= $tab === 'addresses' ? 'var(--brand-deep)' : 'transparent' ?>; border-bottom: 1px solid #F0EEF5;">
                            <span>03 // Delivery Addresses</span>
                            <span style="font-family: var(--font-mono); font-size: 11px;">&rarr;</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('wishlist') ?>" 
                           style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; text-decoration: none; font-size: 13px; font-weight: 600; color: var(--text-primary); border-bottom: 1px solid #F0EEF5;">
                            <span>04 // Saved Wishlist</span>
                            <span style="font-family: var(--font-mono); font-size: 11px;">&nearr;</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('account/logout') ?>" 
                           style="display: block; padding: 14px 20px; text-decoration: none; font-size: 13px; font-weight: 600; color: #EF4444;">
                            05 // Sign Out
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Main Content Area -->
            <div>
                <?php if ($tab === 'profile'): ?>
                    <!-- TAB 1: PROFILE & LOYALTY -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px;">
                        <!-- Loyalty Points -->
                        <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 24px; position: relative; border-top: 3px solid var(--brand-deep);">
                            <div style="font-family: var(--font-mono); font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 8px;">
                                LOYALTY POINTS
                            </div>
                            <div style="font-size: 32px; font-weight: 800; color: var(--brand-deep); line-height: 1.1;">
                                <?= number_format((int)($customer['points_balance'] ?? 0)) ?>
                            </div>
                            <div style="font-size: 12px; color: #8C869B; margin-top: 8px;">
                                Tier: <strong><?= e($customer['loyalty_tier'] ?: 'Standard') ?></strong> (1 pt = Rs. 1.00)
                            </div>
                        </div>

                        <!-- Orders Count -->
                        <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 24px; position: relative; border-top: 3px solid #0E0C14;">
                            <div style="font-family: var(--font-mono); font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 8px;">
                                LIFETIME ORDERS
                            </div>
                            <div style="font-size: 32px; font-weight: 800; color: #0E0C14; line-height: 1.1;">
                                <?= (int)($customer['total_orders_count'] ?? count($customerOrders)) ?>
                            </div>
                            <div style="font-size: 12px; color: #8C869B; margin-top: 8px;">
                                Verified doorstep dispatches
                            </div>
                        </div>

                        <!-- Total Spend -->
                        <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 24px; position: relative; border-top: 3px solid #10B981;">
                            <div style="font-family: var(--font-mono); font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 8px;">
                                TOTAL SPENT
                            </div>
                            <div style="font-size: 26px; font-weight: 800; color: #0E0C14; line-height: 1.3;">
                                Rs. <?= number_format((float)($customer['total_spent_amount'] ?? 0), 2) ?>
                            </div>
                            <div style="font-size: 12px; color: #8C869B; margin-top: 8px;">
                                Across verified purchases
                            </div>
                        </div>
                    </div>

                    <!-- Profile Details Box -->
                    <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 32px;">
                        <h3 style="font-size: 16px; font-weight: 800; text-transform: uppercase; margin: 0 0 20px; letter-spacing: 0.04em; border-bottom: 1px solid var(--border-crisp); padding-bottom: 12px;">
                            PERSONAL PROFILE SPECIFICATIONS
                        </h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; font-size: 14px;">
                            <div>
                                <span style="font-family: var(--font-mono); font-size: 11px; color: #8C869B; display:block; margin-bottom: 4px;">FULL NAME:</span>
                                <strong><?= e($customer['full_name'] ?: 'Not specified') ?></strong>
                            </div>

                            <div>
                                <span style="font-family: var(--font-mono); font-size: 11px; color: #8C869B; display:block; margin-bottom: 4px;">REGISTERED PHONE:</span>
                                <strong style="font-family: var(--font-mono);"><?= e($customer['phone']) ?></strong>
                            </div>

                            <div>
                                <span style="font-family: var(--font-mono); font-size: 11px; color: #8C869B; display:block; margin-bottom: 4px;">EMAIL ADDRESS:</span>
                                <strong><?= e($customer['email'] ?: 'No email registered') ?></strong>
                            </div>

                            <div>
                                <span style="font-family: var(--font-mono); font-size: 11px; color: #8C869B; display:block; margin-bottom: 4px;">MEMBER SINCE:</span>
                                <strong style="font-family: var(--font-mono);"><?= date('F d, Y', strtotime($customer['created_at'])) ?></strong>
                            </div>
                        </div>
                    </div>

                <?php elseif ($tab === 'orders'): ?>
                    <!-- TAB 2: MY ORDERS -->
                    <div style="background: #FFFFFF; border: 1px solid var(--border-crisp);">
                        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-crisp); display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="font-size: 15px; font-weight: 800; text-transform: uppercase; margin: 0; letter-spacing: 0.04em;">
                                MY ORDERS & SHIPMENT RECEIPTS
                            </h3>
                            <span style="font-family: var(--font-mono); font-size: 11px; color: #716B80;">
                                <?= count($customerOrders) ?> ORDERS FOUND
                            </span>
                        </div>

                        <?php if (empty($customerOrders)): ?>
                            <div style="text-align: center; padding: 60px 20px; color: #8C869B;">
                                <div style="font-size: 14px; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">NO ORDERS YET</div>
                                <p style="font-size: 13px; margin-bottom: 20px;">You haven't placed any orders with this phone number yet.</p>
                                <a href="<?= base_url('shop') ?>" class="btn btn--brand btn--sm" style="border-radius:0;">START SHOPPING &rarr;</a>
                            </div>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                                    <thead>
                                        <tr style="background: #FAF9FD; border-bottom: 1px solid var(--border-crisp); font-family: var(--font-mono); font-size: 10px; color: #716B80; text-transform: uppercase; letter-spacing: 0.1em;">
                                            <th style="padding: 12px 20px;">Order #</th>
                                            <th style="padding: 12px 20px;">Date</th>
                                            <th style="padding: 12px 20px;">Items</th>
                                            <th style="padding: 12px 20px;">Total</th>
                                            <th style="padding: 12px 20px;">Status</th>
                                            <th style="padding: 12px 20px; text-align: right;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customerOrders as $ord): ?>
                                            <tr style="border-bottom: 1px solid #F0EEF5;">
                                                <td style="padding: 16px 20px;">
                                                    <strong style="font-family: var(--font-mono); font-size: 12px;"><?= e($ord['order_number']) ?></strong>
                                                </td>
                                                <td style="padding: 16px 20px; font-size: 12px; color: #6B6578;">
                                                    <?= date('M d, Y', strtotime($ord['ordered_at'])) ?>
                                                </td>
                                                <td style="padding: 16px 20px; font-family: var(--font-mono); font-size: 11px;">
                                                    <?= (int)$ord['total_items'] ?> item<?= $ord['total_items'] == 1 ? '' : 's' ?>
                                                </td>
                                                <td style="padding: 16px 20px;">
                                                    <strong style="font-family: var(--font-mono);">Rs. <?= number_format($ord['grand_total'], 2) ?></strong>
                                                </td>
                                                <td style="padding: 16px 20px;">
                                                    <span style="font-family: var(--font-mono); font-size: 10px; text-transform: uppercase; padding: 2px 7px; background: rgba(78,42,132,0.1); color: var(--brand-deep); font-weight: 700;">
                                                        <?= strtoupper(str_replace('_', ' ', $ord['order_status'])) ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 16px 20px; text-align: right;">
                                                    <a href="<?= base_url('track-order?order=' . urlencode($ord['order_number'])) ?>" class="btn btn--outline btn--sm" style="padding: 4px 10px; font-size: 10px; border-radius: 0; text-decoration: none;">
                                                        TRACK LIVE &rarr;
                                                    </a>
                                                    <a href="<?= base_url('invoice?order=' . urlencode($ord['order_number'])) ?>" target="_blank" class="btn btn--sm" style="padding: 4px 10px; font-size: 10px; border-radius: 0; text-decoration: none; margin-left: 4px;">
                                                        INVOICE &nearr;
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($tab === 'addresses'): ?>
                    <!-- TAB 3: ADDRESSES -->
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 32px; align-items: start;">
                        <!-- Saved Addresses List -->
                        <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 24px;">
                            <h3 style="font-size: 15px; font-weight: 800; text-transform: uppercase; margin: 0 0 16px; letter-spacing: 0.04em;">
                                SAVED DELIVERY DESTINATIONS
                            </h3>

                            <?php if (empty($addresses)): ?>
                                <div style="text-align: center; padding: 40px 16px; color: #8C869B; border: 1px dashed var(--border-crisp);">
                                    No saved addresses found. Add an address using the form to fast-track your future checkout orders.
                                </div>
                            <?php else: ?>
                                <div style="display: flex; flex-direction: column; gap: 16px;">
                                    <?php foreach ($addresses as $addr): ?>
                                        <div style="border: 1px solid var(--border-crisp); padding: 18px; position: relative;">
                                            <?php if (!empty($addr['is_default'])): ?>
                                                <span style="position: absolute; top: 14px; right: 14px; font-family: var(--font-mono); font-size: 9px; letter-spacing: 0.1em; background: #D1FAE5; color: #065F46; padding: 2px 6px;">
                                                    DEFAULT ADDRESS
                                                </span>
                                            <?php endif; ?>

                                            <div style="font-weight: 700; font-size: 14px; margin-bottom: 2px;">
                                                <?= e($addr['recipient_name']) ?> (<?= e($addr['address_label']) ?>)
                                            </div>
                                            <div style="font-family: var(--font-mono); font-size: 12px; color: #716B80; margin-bottom: 8px;">
                                                <?= e($addr['recipient_phone']) ?>
                                            </div>
                                            <div style="font-size: 13px; color: var(--text-secondary); line-height: 1.4;">
                                                <?= e($addr['address_line1']) ?><br>
                                                <?php if (!empty($addr['address_line2'])): ?>
                                                    <?= e($addr['address_line2']) ?><br>
                                                <?php endif; ?>
                                                <?= e($addr['city']) ?>, <?= e($addr['district']) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Add New Address Form -->
                        <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 28px;">
                            <h3 style="font-size: 15px; font-weight: 800; text-transform: uppercase; margin: 0 0 16px; letter-spacing: 0.04em;">
                                ADD NEW ADDRESS
                            </h3>

                            <div id="address-feedback" style="display:none; padding:10px 12px; font-size:12px; margin-bottom:14px;"></div>

                            <form id="form-add-address">
                                <div style="margin-bottom: 12px;">
                                    <label class="form-label" style="font-size:10px; display:block; margin-bottom:4px;">RECIPIENT NAME <span style="color:#EF4444;">*</span></label>
                                    <input type="text" name="recipient_name" value="<?= e($customer['full_name'] ?? '') ?>" required
                                           style="width:100%; padding:9px 12px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; box-sizing:border-box;">
                                </div>

                                <div style="margin-bottom: 12px;">
                                    <label class="form-label" style="font-size:10px; display:block; margin-bottom:4px;">RECIPIENT PHONE <span style="color:#EF4444;">*</span></label>
                                    <input type="tel" name="recipient_phone" value="<?= e($customer['phone'] ?? '') ?>" required
                                           style="width:100%; padding:9px 12px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; box-sizing:border-box;">
                                </div>

                                <div style="margin-bottom: 12px;">
                                    <label class="form-label" style="font-size:10px; display:block; margin-bottom:4px;">STREET ADDRESS / HOUSE # <span style="color:#EF4444;">*</span></label>
                                    <input type="text" name="address_line1" placeholder="No. 124, High Level Road" required
                                           style="width:100%; padding:9px 12px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; box-sizing:border-box;">
                                </div>

                                <div style="margin-bottom: 12px;">
                                    <label class="form-label" style="font-size:10px; display:block; margin-bottom:4px;">APARTMENT / SUITE (OPTIONAL)</label>
                                    <input type="text" name="address_line2" placeholder="Suite 4B"
                                           style="width:100%; padding:9px 12px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; box-sizing:border-box;">
                                </div>

                                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                                    <div>
                                        <label class="form-label" style="font-size:10px; display:block; margin-bottom:4px;">CITY <span style="color:#EF4444;">*</span></label>
                                        <input type="text" name="city" placeholder="e.g. Nugegoda" required
                                               style="width:100%; padding:9px 12px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; box-sizing:border-box;">
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-size:10px; display:block; margin-bottom:4px;">DISTRICT <span style="color:#EF4444;">*</span></label>
                                        <select name="district" required style="width:100%; padding:9px 12px; border:1px solid var(--border-crisp); font-size:13px; border-radius:0; background:#FFF; box-sizing:border-box;">
                                            <?php foreach ($districts as $d): ?>
                                                <option value="<?= e($d) ?>" <?= $d === 'Colombo' ? 'selected' : '' ?>><?= e($d) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div style="margin-bottom: 16px;">
                                    <label style="display:flex; align-items:center; gap:8px; font-size:12px; cursor:pointer;">
                                        <input type="checkbox" name="is_default" value="1" checked>
                                        <span>Set as default delivery address for 1-click checkout</span>
                                    </label>
                                </div>

                                <button type="submit" id="btn-save-address" class="btn btn--brand btn--full" style="padding:11px; font-size:11px; letter-spacing:0.1em; border-radius:0;">
                                    SAVE DELIVERY ADDRESS &rarr;
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    // Save Address Handler
    const addrForm = document.getElementById('form-add-address');
    if (addrForm) {
        addrForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-save-address');
            const fb = document.getElementById('address-feedback');
            btn.disabled = true;
            btn.textContent = 'SAVING...';
            fb.style.display = 'none';

            const formData = new FormData(this);

            fetch('<?= base_url('ajax/save_address.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.textContent = 'SAVE DELIVERY ADDRESS →';

                if (data.success) {
                    fb.style.background = '#D1FAE5';
                    fb.style.color = '#065F46';
                    fb.textContent = data.message;
                    fb.style.display = 'block';
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    fb.style.background = '#FEE2E2';
                    fb.style.color = '#991B1B';
                    fb.textContent = data.message || 'Error saving address.';
                    fb.style.display = 'block';
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = 'SAVE DELIVERY ADDRESS →';
                fb.style.background = '#FEE2E2';
                fb.style.color = '#991B1B';
                fb.textContent = 'Network error saving address.';
                fb.style.display = 'block';
            });
        });
    }
</script>
