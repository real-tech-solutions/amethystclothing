<?php
/**
 * Amethystclothing.lk — Admin Inventory & Stock Control
 * Sharp Minimalist Luxury UI — Strictly No AI Slop
 */

require_once __DIR__ . '/../../../core/helpers.php';
require_admin_auth();

$pageTitle = 'Inventory & Stock // AMETHYST ADMIN';

// Filter & Search Params
$searchQuery = trim($_GET['q'] ?? '');
$filterCategory = trim($_GET['cat'] ?? '');
$stockFilter = trim($_GET['stock'] ?? ''); // 'low', 'out', or ''

// Categories for filter
$categories = [];
try {
    $categories = db()->query("SELECT id, name, slug FROM categories ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {}

// Build Products Query
$where = ["p.status = 'active'"];
$params = [];

if (!empty($searchQuery)) {
    $where[] = "(p.name LIKE :q OR p.base_sku_prefix LIKE :q OR pv.sku LIKE :q)";
    $params['q'] = '%' . $searchQuery . '%';
}

if (!empty($filterCategory)) {
    $where[] = "c.slug = :cat";
    $params['cat'] = $filterCategory;
}

if ($stockFilter === 'low') {
    $where[] = "pv.total_stock_quantity <= 5 AND pv.total_stock_quantity > 0";
} else if ($stockFilter === 'out') {
    $where[] = "pv.total_stock_quantity = 0";
}

$whereSql = implode(' AND ', $where);

// Fetch products with their variants
$products = [];
try {
    $sql = "
        SELECT DISTINCT p.id, p.name, p.slug, p.base_sku_prefix, p.base_price, c.name as category_name
        FROM products p
        LEFT JOIN product_categories pc ON pc.product_id = p.id
        LEFT JOIN categories c ON c.id = pc.category_id
        LEFT JOIN product_variants pv ON pv.product_id = p.id
        WHERE {$whereSql}
        ORDER BY p.name ASC
    ";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Fetch variants for each matching product
    foreach ($products as &$prod) {
        $vSql = "
            SELECT id, sku, color_name, color_hex, size_code, total_stock_quantity, low_stock_threshold, is_active
            FROM product_variants
            WHERE product_id = ?
            ORDER BY sort_order ASC, size_code ASC
        ";
        $vStmt = db()->prepare($vSql);
        $vStmt->execute([$prod['id']]);
        $prod['variants'] = $vStmt->fetchAll();

        // Calculate total product stock
        $prod['total_stock'] = array_sum(array_column($prod['variants'], 'total_stock_quantity'));
    }
    unset($prod);
} catch (Exception $e) {
    $products = [];
}

// Global stats
$totalUnits = 0;
$totalVariants = 0;
$totalLowStock = 0;
try {
    $totalUnits = (int) db()->query("SELECT COALESCE(SUM(total_stock_quantity), 0) FROM product_variants WHERE is_active = 1")->fetchColumn();
    $totalVariants = (int) db()->query("SELECT COUNT(*) FROM product_variants WHERE is_active = 1")->fetchColumn();
    $totalLowStock = (int) db()->query("SELECT COUNT(*) FROM product_variants WHERE total_stock_quantity <= 5 AND is_active = 1")->fetchColumn();
} catch (Exception $e) {}
?>

<div class="admin-page-header">
    <div>
        <div class="admin-breadcrumb">OPERATIONS // WAREHOUSE INVENTORY</div>
        <h1 class="admin-heading">Inventory & Stock Control</h1>
    </div>
    <div class="admin-header-actions">
        <a href="<?= base_url('admin/products') ?>" class="btn-admin btn-admin-outline btn-admin-sm">
            REFRESH INVENTORY
        </a>
    </div>
</div>

<!-- Mini Stats Overview -->
<div class="admin-metrics-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 24px;">
    <div class="metric-card accent-amethyst" style="padding:18px 20px;">
        <div class="metric-label" style="font-size:10px;">TOTAL UNITS IN STOCK</div>
        <div class="metric-value" style="font-size:26px;"><?= number_format($totalUnits) ?></div>
        <div class="metric-sub"><?= $totalVariants ?> Active Product SKUs</div>
    </div>

    <div class="metric-card accent-warning" style="padding:18px 20px;">
        <div class="metric-label" style="font-size:10px;">LOW STOCK ALERTS (&le; 5)</div>
        <div class="metric-value" style="font-size:26px;"><?= $totalLowStock ?></div>
        <div class="metric-sub">Variants needing urgent printing/restock</div>
    </div>

    <div class="metric-card accent-success" style="padding:18px 20px;">
        <div class="metric-label" style="font-size:10px;">TOTAL CATALOG PRODUCTS</div>
        <div class="metric-value" style="font-size:26px;"><?= count($products) ?></div>
        <div class="metric-sub">Active streetwear silhouettes</div>
    </div>
</div>

<!-- Search & Filters -->
<div class="admin-card" style="padding: 18px 24px; margin-bottom: 24px;">
    <form method="GET" action="<?= base_url('admin/products') ?>" style="display:flex;flex-wrap:wrap;gap:14px;align-items:center;justify-content:space-between;">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
            <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="Search SKU or Product Name..." 
                   style="padding:9px 14px;border:1px solid #D1CADB;font-size:13px;width:240px;outline:none;border-radius:0;">
            
            <select name="cat" style="padding:9px 14px;border:1px solid #D1CADB;font-size:13px;outline:none;background:#FFFFFF;border-radius:0;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat['slug']) ?>" <?= $filterCategory === $cat['slug'] ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="stock" style="padding:9px 14px;border:1px solid #D1CADB;font-size:13px;outline:none;background:#FFFFFF;border-radius:0;">
                <option value="">All Stock Levels</option>
                <option value="low" <?= $stockFilter === 'low' ? 'selected' : '' ?>>Low Stock (&le; 5)</option>
                <option value="out" <?= $stockFilter === 'out' ? 'selected' : '' ?>>Out of Stock (0)</option>
            </select>

            <button type="submit" class="btn-admin btn-admin-sm">APPLY FILTER</button>
            <?php if (!empty($searchQuery) || !empty($filterCategory) || !empty($stockFilter)): ?>
                <a href="<?= base_url('admin/products') ?>" class="btn-admin btn-admin-outline btn-admin-sm">RESET</a>
            <?php endif; ?>
        </div>

        <div style="font-family:'Space Mono';font-size:11px;color:#716B80;">
            SHOWING <?= count($products) ?> PRODUCT CATALOGS
        </div>
    </form>
</div>

<!-- Products and Variants List -->
<?php if (empty($products)): ?>
    <div class="admin-card" style="text-align:center;padding:48px;color:#8C869B;">
        No products or variants matched your query.
    </div>
<?php else: ?>
    <?php foreach ($products as $prod): ?>
        <div class="admin-card" style="margin-bottom: 24px;">
            <!-- Product Card Header -->
            <div class="admin-card-header" style="background:#FAF9FD;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-family:'Space Mono';font-size:11px;background:#0E0C14;color:#FFF;padding:2px 8px;letter-spacing:0.1em;">
                            <?= e($prod['base_sku_prefix']) ?>
                        </span>
                        <h2 style="font-size:16px;margin:0;font-weight:800;letter-spacing:0.02em;color:#0E0C14;">
                            <?= e($prod['name']) ?>
                        </h2>
                        <?php if (!empty($prod['category_name'])): ?>
                            <span style="font-size:11px;background:#EDE9FE;color:#5B21B6;padding:2px 8px;font-family:'Space Mono';">
                                <?= e($prod['category_name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:16px;font-size:13px;">
                    <div>
                        <span style="color:#716B80;font-size:11px;">BASE PRICE:</span>
                        <strong>Rs. <?= number_format($prod['base_price'], 2) ?></strong>
                    </div>
                    <div>
                        <span style="color:#716B80;font-size:11px;">TOTAL STOCK:</span>
                        <strong style="font-family:'Space Mono';"><?= (int)$prod['total_stock'] ?> units</strong>
                    </div>
                    <a href="<?= base_url('product/' . $prod['slug']) ?>" target="_blank" class="btn-admin btn-admin-outline btn-admin-sm">
                        VIEW STORE &nearr;
                    </a>
                </div>
            </div>

            <!-- Variants Table -->
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:25%;">Variant SKU</th>
                            <th style="width:20%;">Color</th>
                            <th style="width:10%;">Size</th>
                            <th style="width:15%;">Status</th>
                            <th style="width:30%;text-align:right;">Stock Quantity Quick Adjust</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prod['variants'] as $var): ?>
                            <?php
                                $stock = (int)$var['total_stock_quantity'];
                                $badgeClass = 'badge-instock';
                                $badgeText = 'IN STOCK';
                                if ($stock === 0) {
                                    $badgeClass = 'badge-outofstock';
                                    $badgeText = 'OUT OF STOCK';
                                } else if ($stock <= 5) {
                                    $badgeClass = 'badge-lowstock';
                                    $badgeText = 'LOW STOCK (' . $stock . ')';
                                }
                            ?>
                            <tr id="variant-row-<?= $var['id'] ?>">
                                <td>
                                    <strong style="font-family:'Space Mono';font-size:12px;letter-spacing:0.05em;">
                                        <?= e($var['sku']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <span style="display:inline-block;width:14px;height:14px;background-color:<?= e($var['color_hex'] ?: '#000') ?>;border:1px solid #CCC;"></span>
                                        <span><?= e($var['color_name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-family:'Space Mono';font-weight:700;font-size:12px;background:#F4F2F8;padding:3px 8px;border:1px solid #E4E0EC;">
                                        <?= e($var['size_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-status-badge <?= $badgeClass ?>" id="var-badge-<?= $var['id'] ?>">
                                        <?= $badgeText ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:inline-flex;align-items:center;gap:6px;">
                                        <button type="button" class="btn-admin btn-admin-outline btn-admin-sm btn-step-stock" data-id="<?= $var['id'] ?>" data-step="-1" style="padding:6px 10px;font-weight:bold;">&minus;</button>
                                        
                                        <input type="number" id="stock-input-<?= $var['id'] ?>" value="<?= $stock ?>" min="0" 
                                               style="width:70px;padding:6px 8px;text-align:center;font-family:'Space Mono';font-weight:700;font-size:13px;border:1px solid #D1CADB;border-radius:0;outline:none;">
                                        
                                        <button type="button" class="btn-admin btn-admin-outline btn-admin-sm btn-step-stock" data-id="<?= $var['id'] ?>" data-step="1" style="padding:6px 10px;font-weight:bold;">&plus;</button>
                                        
                                        <button type="button" class="btn-admin btn-admin-amethyst btn-admin-sm btn-save-stock" data-id="<?= $var['id'] ?>" style="margin-left:4px;">
                                            SAVE
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
    // Step buttons (+ / -)
    document.querySelectorAll('.btn-step-stock').forEach(btn => {
        btn.addEventListener('click', function() {
            const varId = this.dataset.id;
            const step = parseInt(this.dataset.step, 10);
            const input = document.getElementById('stock-input-' + varId);
            let val = parseInt(input.value, 10) || 0;
            val = Math.max(0, val + step);
            input.value = val;
        });
    });

    // Save Stock via AJAX
    document.querySelectorAll('.btn-save-stock').forEach(btn => {
        btn.addEventListener('click', function() {
            const varId = this.dataset.id;
            const input = document.getElementById('stock-input-' + varId);
            const stockQty = parseInt(input.value, 10);

            if (isNaN(stockQty) || stockQty < 0) {
                showAdminToast('Please specify a valid non-negative stock quantity.', 'error');
                return;
            }

            const originalText = this.textContent;
            this.disabled = true;
            this.textContent = '...';

            const formData = new FormData();
            formData.append('variant_id', varId);
            formData.append('stock_quantity', stockQty);

            fetch('<?= base_url('ajax/update_stock.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                this.textContent = originalText;

                if (data.success) {
                    showAdminToast(data.message, 'success');
                    // Update badge dynamically
                    const badge = document.getElementById('var-badge-' + varId);
                    if (badge) {
                        badge.className = 'admin-status-badge ' + data.badge_class;
                        badge.textContent = data.status_text;
                    }
                } else {
                    showAdminToast(data.message || 'Error updating stock', 'error');
                }
            })
            .catch(err => {
                this.disabled = false;
                this.textContent = originalText;
                showAdminToast('Network error while updating stock', 'error');
            });
        });
    });
</script>
