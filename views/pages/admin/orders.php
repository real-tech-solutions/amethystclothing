<?php
/**
 * Amethystclothing.lk — Admin Order Management
 * Sharp Minimalist Luxury UI — Strictly No AI Slop
 */

require_once __DIR__ . '/../../../core/helpers.php';
require_admin_auth();

$pageTitle = 'Orders Management // AMETHYST ADMIN';

// Filter & Search Params
$filterStatus = trim($_GET['status'] ?? '');
$searchQuery  = trim($_GET['q'] ?? '');
$selectedOrderNum = trim($_GET['order'] ?? '');

// Build query
$where = [];
$params = [];

if (!empty($filterStatus)) {
    $where[] = "o.order_status = :status";
    $params['status'] = $filterStatus;
}

if (!empty($searchQuery)) {
    $where[] = "(o.order_number LIKE :q OR o.customer_phone LIKE :q OR o.recipient_name LIKE :q)";
    $params['q'] = '%' . $searchQuery . '%';
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$orders = [];
try {
    $sql = "
        SELECT o.*, 
               (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
        FROM orders o
        {$whereSql}
        ORDER BY o.ordered_at DESC
    ";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
} catch (Exception $e) {
    $orders = [];
}

// If an order is selected, fetch full details + items + history
$activeOrder = null;
$activeOrderItems = [];
$activeOrderHistory = [];

if (!empty($selectedOrderNum)) {
    try {
        $stmt = db()->prepare("SELECT * FROM orders WHERE order_number = ? LIMIT 1");
        $stmt->execute([$selectedOrderNum]);
        $activeOrder = $stmt->fetch();

        if ($activeOrder) {
            $stmtItems = db()->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmtItems->execute([$activeOrder['id']]);
            $activeOrderItems = $stmtItems->fetchAll();

            $stmtHist = db()->prepare("
                SELECT osh.*, a.name as admin_name
                FROM order_status_history osh
                LEFT JOIN admins a ON a.id = osh.updated_by_admin_id
                WHERE osh.order_id = ?
                ORDER BY osh.created_at DESC
            ");
            $stmtHist->execute([$activeOrder['id']]);
            $activeOrderHistory = $stmtHist->fetchAll();
        }
    } catch (Exception $e) {}
}

// Status options
$allStatuses = [
    'placed' => 'Order Placed',
    'confirmed' => 'Confirmed & Approved',
    'printing_processing' => 'Printing / Production',
    'packed' => 'Quality Inspected & Packed',
    'handed_to_courier' => 'Handed to Courier',
    'out_for_delivery' => 'Out for Delivery',
    'delivered' => 'Delivered to Customer',
    'cancelled' => 'Cancelled',
    'returned' => 'Returned'
];
?>

<div class="admin-page-header">
    <div>
        <div class="admin-breadcrumb">OPERATIONS // FULFILLMENT</div>
        <h1 class="admin-heading">Orders Management</h1>
    </div>
    <div class="admin-header-actions">
        <a href="<?= base_url('admin/orders') ?>" class="btn-admin btn-admin-outline btn-admin-sm">
            REFRESH LIST
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="admin-card" style="padding: 18px 24px; margin-bottom: 24px;">
    <form method="GET" action="<?= base_url('admin/orders') ?>" style="display:flex;flex-wrap:wrap;gap:16px;align-items:center;justify-content:space-between;">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
            <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="Search Order # or Phone..." 
                   style="padding:9px 14px;border:1px solid #D1CADB;font-size:13px;width:240px;outline:none;border-radius:0;">
            
            <select name="status" style="padding:9px 14px;border:1px solid #D1CADB;font-size:13px;outline:none;background:#FFFFFF;border-radius:0;">
                <option value="">All Statuses</option>
                <?php foreach ($allStatuses as $stKey => $stLabel): ?>
                    <option value="<?= $stKey ?>" <?= $filterStatus === $stKey ? 'selected' : '' ?>>
                        <?= $stLabel ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-admin btn-admin-sm">FILTER</button>
            <?php if (!empty($searchQuery) || !empty($filterStatus)): ?>
                <a href="<?= base_url('admin/orders') ?>" class="btn-admin btn-admin-outline btn-admin-sm">RESET</a>
            <?php endif; ?>
        </div>

        <div style="font-family:'Space Mono';font-size:11px;color:#716B80;">
            SHOWING <?= count($orders) ?> ORDER<?= count($orders) === 1 ? '' : 'S' ?>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="admin-card">
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Grand Total</th>
                    <th>Payment</th>
                    <th>Fulfillment Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:48px;color:#8C869B;">
                            No matching orders found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $ord): ?>
                        <tr style="<?= ($activeOrder && $activeOrder['id'] == $ord['id']) ? 'background:#F3EFF9;' : '' ?>">
                            <td>
                                <strong style="font-family:'Space Mono';font-size:12px;"><?= e($ord['order_number']) ?></strong>
                            </td>
                            <td style="font-size:12px;color:#6B6578;">
                                <?= date('Y-m-d H:i', strtotime($ord['ordered_at'])) ?>
                            </td>
                            <td>
                                <div style="font-weight:600;"><?= e($ord['recipient_name']) ?></div>
                                <div style="font-family:'Space Mono';font-size:11px;color:#716B80;">
                                    <?= e($ord['customer_phone']) ?> &bull; <?= e($ord['shipping_city'] ?? $ord['shipping_district']) ?>
                                </div>
                            </td>
                            <td style="font-family:'Space Mono';font-size:11px;">
                                <?= (int)$ord['item_count'] ?> item<?= $ord['item_count'] == 1 ? '' : 's' ?>
                            </td>
                            <td>
                                <strong>Rs. <?= number_format($ord['grand_total'], 2) ?></strong>
                            </td>
                            <td>
                                <span style="font-family:'Space Mono';font-size:10px;text-transform:uppercase;padding:2px 6px;border:1px solid <?= $ord['payment_status'] === 'paid' ? '#10B981' : '#F59E0B' ?>;color:<?= $ord['payment_status'] === 'paid' ? '#065F46' : '#92400E' ?>;">
                                    <?= e($ord['payment_status']) ?> (<?= strtoupper($ord['payment_method']) ?>)
                                </span>
                            </td>
                            <td>
                                <span class="admin-status-badge badge-<?= e($ord['order_status']) ?>" id="status-badge-<?= $ord['id'] ?>">
                                    <?= strtoupper(str_replace('_', ' ', $ord['order_status'])) ?>
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <a href="<?= base_url('admin/orders?order=' . urlencode($ord['order_number'])) ?><?= !empty($filterStatus) ? '&status=' . urlencode($filterStatus) : '' ?>" 
                                   class="btn-admin btn-admin-outline btn-admin-sm">
                                    MANAGE &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Order Detail Slide-Out / Drawer / Modal -->
<?php if ($activeOrder): ?>
<div id="order-drawer-overlay" style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(11,9,16,0.6);z-index:900;display:flex;justify-content:flex-end;">
    <div style="background:#FFFFFF;width:100%;max-width:680px;height:100%;overflow-y:auto;box-shadow:-4px 0 24px rgba(0,0,0,0.2);display:flex;flex-direction:column;">
        
        <!-- Drawer Header -->
        <div style="padding:24px 28px;background:#0E0C14;color:#FFFFFF;display:flex;align-items:center;justify-content:space-between;border-bottom:3px solid #4e2a84;">
            <div>
                <div style="font-family:'Space Mono';font-size:11px;color:#9966cc;letter-spacing:0.1em;text-transform:uppercase;">
                    ORDER FULFILLMENT FILE
                </div>
                <h2 style="font-size:20px;margin:4px 0 0;font-weight:800;letter-spacing:0.04em;">
                    <?= e($activeOrder['order_number']) ?>
                </h2>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <a href="<?= base_url('invoice?order=' . urlencode($activeOrder['order_number'])) ?>" target="_blank" class="btn-admin btn-admin-outline btn-admin-sm" style="color:#FFF;border-color:#443D54;">
                    PRINT INVOICE &nearr;
                </a>
                <a href="<?= base_url('admin/orders') ?><?= !empty($filterStatus) ? '?status=' . urlencode($filterStatus) : '' ?>" 
                   style="color:#A9A3B5;text-decoration:none;font-size:24px;line-height:1;padding:4px 8px;">
                    &times;
                </a>
            </div>
        </div>

        <div style="padding:28px;flex:1;">

            <!-- Status Updater Box -->
            <div style="background:#FAF9FD;border:1px solid #E4E0EC;padding:20px;margin-bottom:28px;">
                <div style="font-family:'Space Mono';font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:#4e2a84;font-weight:700;margin-bottom:14px;">
                    UPDATE ORDER & COURIER STATUS
                </div>

                <form id="order-status-update-form">
                    <input type="hidden" name="order_id" value="<?= (int)$activeOrder['id'] ?>">
                    <input type="hidden" name="order_number" value="<?= e($activeOrder['order_number']) ?>">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                        <div>
                            <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                                Order Status
                            </label>
                            <select name="order_status" id="select-order-status" style="width:100%;padding:10px;border:1px solid #D1CADB;font-size:13px;border-radius:0;background:#FFF;font-weight:600;">
                                <?php foreach ($allStatuses as $stKey => $stLabel): ?>
                                    <option value="<?= $stKey ?>" <?= $activeOrder['order_status'] === $stKey ? 'selected' : '' ?>>
                                        <?= $stLabel ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                                Payment Status
                            </label>
                            <select name="payment_status" id="select-payment-status" style="width:100%;padding:10px;border:1px solid #D1CADB;font-size:13px;border-radius:0;background:#FFF;">
                                <option value="unpaid" <?= $activeOrder['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                                <option value="paid" <?= $activeOrder['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid (Complete)</option>
                                <option value="refunded" <?= $activeOrder['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                        <div>
                            <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                                Courier Partner
                            </label>
                            <input type="text" name="courier_partner" value="<?= e($activeOrder['courier_partner'] ?? '') ?>" placeholder="e.g. PromptX, Fardar, Koombiyo" 
                                   style="width:100%;padding:10px;border:1px solid #D1CADB;font-size:13px;border-radius:0;box-sizing:border-box;">
                        </div>

                        <div>
                            <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                                Courier Tracking #
                            </label>
                            <input type="text" name="courier_tracking_number" value="<?= e($activeOrder['courier_tracking_number'] ?? '') ?>" placeholder="e.g. PRX-9482910" 
                                   style="width:100%;padding:10px;border:1px solid #D1CADB;font-size:13px;border-radius:0;box-sizing:border-box;">
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                            Public Timeline Note / Message
                        </label>
                        <input type="text" name="public_comment" placeholder="Optional note visible on tracking page..." 
                               style="width:100%;padding:10px;border:1px solid #D1CADB;font-size:13px;border-radius:0;box-sizing:border-box;">
                    </div>

                    <button type="submit" id="btn-save-status" class="btn-admin btn-admin-amethyst" style="width:100%;justify-content:center;padding:12px;">
                        SAVE & BROADCAST STATUS UPDATE &rarr;
                    </button>
                </form>
            </div>

            <!-- Customer & Shipping Information -->
            <div style="margin-bottom:28px;">
                <div style="font-family:'Space Mono';font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:#6B6578;border-bottom:1px solid #E4E0EC;padding-bottom:6px;margin-bottom:12px;">
                    CUSTOMER & SHIPPING DESTINATION
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:13px;">
                    <div>
                        <div style="color:#716B80;font-size:11px;">RECIPIENT NAME</div>
                        <div style="font-weight:700;font-size:14px;margin-top:2px;"><?= e($activeOrder['recipient_name']) ?></div>
                        
                        <div style="color:#716B80;font-size:11px;margin-top:10px;">PHONE NUMBER</div>
                        <div style="font-family:'Space Mono';font-weight:700;margin-top:2px;"><?= e($activeOrder['customer_phone']) ?></div>
                    </div>
                    <div>
                        <div style="color:#716B80;font-size:11px;">DELIVERY ADDRESS</div>
                        <div style="margin-top:2px;line-height:1.4;">
                            <?= e($activeOrder['shipping_address_line1']) ?><br>
                            <?php if (!empty($activeOrder['shipping_address_line2'])): ?>
                                <?= e($activeOrder['shipping_address_line2']) ?><br>
                            <?php endif; ?>
                            <?= e($activeOrder['shipping_city']) ?>, <?= e($activeOrder['shipping_district']) ?>
                        </div>

                        <?php if (!empty($activeOrder['delivery_notes'])): ?>
                            <div style="color:#716B80;font-size:11px;margin-top:10px;">DELIVERY NOTES</div>
                            <div style="font-style:italic;color:#454050;margin-top:2px;"><?= e($activeOrder['delivery_notes']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Ordered Items List -->
            <div style="margin-bottom:28px;">
                <div style="font-family:'Space Mono';font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:#6B6578;border-bottom:1px solid #E4E0EC;padding-bottom:6px;margin-bottom:12px;">
                    ORDERED SILHOUETTES & SIZES
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="border-bottom:1px solid #E4E0EC;text-align:left;font-family:'Space Mono';font-size:10px;color:#716B80;">
                            <th style="padding:8px 0;">Item</th>
                            <th style="padding:8px 12px;text-align:center;">Qty</th>
                            <th style="padding:8px 0;text-align:right;">Price</th>
                            <th style="padding:8px 0;text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeOrderItems as $item): ?>
                            <tr style="border-bottom:1px solid #F0EEF5;">
                                <td style="padding:10px 0;">
                                    <div style="font-weight:700;"><?= e($item['product_name_snapshot']) ?></div>
                                    <div style="font-family:'Space Mono';font-size:11px;color:#716B80;">
                                        SKU: <?= e($item['sku_snapshot']) ?> &bull; 
                                        Color: <?= e($item['color_snapshot']) ?> &bull; 
                                        Size: <?= e($item['size_snapshot']) ?>
                                    </div>
                                </td>
                                <td style="padding:10px 12px;text-align:center;font-family:'Space Mono';font-weight:700;">
                                    <?= (int)$item['quantity'] ?>
                                </td>
                                <td style="padding:10px 0;text-align:right;font-family:'Space Mono';">
                                    Rs. <?= number_format($item['unit_price'], 2) ?>
                                </td>
                                <td style="padding:10px 0;text-align:right;font-family:'Space Mono';font-weight:700;">
                                    Rs. <?= number_format($item['line_total'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Financial Totals -->
                <div style="margin-top:16px;padding-top:12px;border-top:1px solid #E4E0EC;display:flex;flex-direction:column;gap:6px;font-size:13px;">
                    <div style="display:flex;justify-content:space-between;color:#6B6578;">
                        <span>Subtotal:</span>
                        <span style="font-family:'Space Mono';">Rs. <?= number_format($activeOrder['subtotal_amount'], 2) ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;color:#6B6578;">
                        <span>Shipping Delivery Fee:</span>
                        <span style="font-family:'Space Mono';">Rs. <?= number_format($activeOrder['shipping_fee'], 2) ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-weight:800;font-size:15px;padding-top:8px;border-top:1px solid #E4E0EC;">
                        <span>Grand Total (<?= strtoupper($activeOrder['payment_method']) ?>):</span>
                        <span style="font-family:'Space Mono';color:#0E0C14;">Rs. <?= number_format($activeOrder['grand_total'], 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Status History Timeline -->
            <div>
                <div style="font-family:'Space Mono';font-size:11px;letter-spacing:0.1em;text-transform:uppercase;color:#6B6578;border-bottom:1px solid #E4E0EC;padding-bottom:6px;margin-bottom:16px;">
                    FULFILLMENT LOG & AUDIT TIMELINE
                </div>
                <div style="display:flex;flex-direction:column;gap:16px;border-left:2px solid #E4E0EC;padding-left:18px;margin-left:8px;">
                    <?php if (empty($activeOrderHistory)): ?>
                        <div style="font-size:12px;color:#8C869B;">No status logs recorded yet.</div>
                    <?php else: ?>
                        <?php foreach ($activeOrderHistory as $hist): ?>
                            <div style="position:relative;">
                                <div style="position:absolute;left:-23px;top:4px;width:8px;height:8px;background:#4e2a84;border-radius:0;"></div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span class="admin-status-badge badge-<?= e($hist['status']) ?>">
                                        <?= strtoupper(str_replace('_', ' ', $hist['status'])) ?>
                                    </span>
                                    <span style="font-family:'Space Mono';font-size:11px;color:#716B80;">
                                        <?= date('M d, Y h:i A', strtotime($hist['created_at'])) ?>
                                    </span>
                                    <?php if (!empty($hist['admin_name'])): ?>
                                        <span style="font-size:11px;color:#9966cc;">by <?= e($hist['admin_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($hist['public_comment'])): ?>
                                    <div style="font-size:12px;color:#454050;margin-top:4px;line-height:1.4;">
                                        <?= e($hist['public_comment']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Status update AJAX
    document.getElementById('order-status-update-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-status');
        const formData = new FormData(this);
        btn.disabled = true;
        btn.textContent = 'UPDATING TIMELINE...';

        fetch('<?= base_url('ajax/update_order_status.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAdminToast(data.message, 'success');
                // Reload after short delay to reflect changes across drawer & table
                setTimeout(() => {
                    window.location.reload();
                }, 600);
            } else {
                showAdminToast(data.message || 'Status update failed', 'error');
                btn.disabled = false;
                btn.textContent = 'SAVE & BROADCAST STATUS UPDATE →';
            }
        })
        .catch(err => {
            showAdminToast('Network error while updating status', 'error');
            btn.disabled = false;
            btn.textContent = 'SAVE & BROADCAST STATUS UPDATE →';
        });
    });

    // Close on ESC key or background click
    document.getElementById('order-drawer-overlay').addEventListener('click', function(e) {
        if (e.target === this) {
            window.location.href = '<?= base_url('admin/orders') ?><?= !empty($filterStatus) ? '?status=' . urlencode($filterStatus) : '' ?>';
        }
    });
</script>
<?php endif; ?>
