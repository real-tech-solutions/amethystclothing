<?php
/**
 * Amethystclothing.lk — Admin Category & Subcategory Management
 * Sharp Minimalist Luxury UI — Strictly No AI Slop
 */

require_once __DIR__ . '/../../../core/helpers.php';
require_admin_auth();

$pageTitle = 'Categories & Hierarchy // AMETHYST ADMIN';

// Fetch all categories with parent names and product counts
$categories = [];
try {
    $stmt = db()->query("
        SELECT c.*, 
               p.name as parent_name,
               (SELECT COUNT(*) FROM product_categories pc WHERE pc.category_id = c.id) as product_count,
               (SELECT COUNT(*) FROM categories sub WHERE sub.parent_id = c.id) as subcategory_count
        FROM categories c
        LEFT JOIN categories p ON p.id = c.parent_id
        ORDER BY COALESCE(c.parent_id, c.id) ASC, (c.parent_id IS NOT NULL) ASC, c.sort_order ASC, c.id ASC
    ");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// Top level categories for parent dropdown
$topLevelCategories = array_filter($categories, fn($c) => empty($c['parent_id']));

// Metric counts
$totalCount = count($categories);
$topLevelCount = count($topLevelCategories);
$subCount = $totalCount - $topLevelCount;
$featuredCount = count(array_filter($categories, fn($c) => !empty($c['is_featured'])));
?>

<div class="admin-page-header">
    <div>
        <div class="admin-breadcrumb">STORE ARCHITECTURE // TAXONOMY</div>
        <h1 class="admin-heading">Categories & Subcategories</h1>
    </div>
    <div class="admin-header-actions">
        <button type="button" class="btn-admin btn-admin-amethyst" id="btn-add-category">
            &plus; CREATE CATEGORY
        </button>
    </div>
</div>

<!-- Metrics Bar -->
<div class="admin-metrics-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
    <div class="metric-card accent-amethyst" style="padding:16px 20px;">
        <div class="metric-label" style="font-size:10px;">TOTAL CATEGORIES</div>
        <div class="metric-value" style="font-size:24px;"><?= $totalCount ?></div>
        <div class="metric-sub"><?= $topLevelCount ?> Top-Level, <?= $subCount ?> Subcategories</div>
    </div>

    <div class="metric-card accent-success" style="padding:16px 20px;">
        <div class="metric-label" style="font-size:10px;">HOMEPAGE FEATURED</div>
        <div class="metric-value" style="font-size:24px;"><?= $featuredCount ?></div>
        <div class="metric-sub">Visible on homepage grid</div>
    </div>

    <div class="metric-card accent-warning" style="padding:16px 20px;">
        <div class="metric-label" style="font-size:10px;">TOP-LEVEL SILHOUETTES</div>
        <div class="metric-value" style="font-size:24px;"><?= $topLevelCount ?></div>
        <div class="metric-sub">In main header navigation</div>
    </div>

    <div class="metric-card" style="padding:16px 20px;">
        <div class="metric-label" style="font-size:10px;">SUBCATEGORIES</div>
        <div class="metric-value" style="font-size:24px;"><?= $subCount ?></div>
        <div class="metric-sub">Nested under parent divisions</div>
    </div>
</div>

<!-- Categories Table -->
<div class="admin-card">
    <div class="admin-card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h2 class="admin-card-title">Category Hierarchy</h2>
        <span style="font-family:'Space Mono';font-size:11px;color:#716B80;">
            ORDERED BY SORT ORDER &bull; HOME GRID CONTROLS
        </span>
    </div>

    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:70px;">Banner</th>
                    <th style="width:25%;">Category Name & Hierarchy</th>
                    <th style="width:15%;">Slug</th>
                    <th style="width:15%;">Parent Category</th>
                    <th style="width:8%;text-align:center;">Products</th>
                    <th style="width:10%;text-align:center;">Sort Order</th>
                    <th style="width:10%;text-align:center;">Home Grid</th>
                    <th style="width:8%;text-align:center;">Status</th>
                    <th style="width:12%;text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center;padding:48px;color:#8C869B;">
                            No categories created yet. Click "+ CREATE CATEGORY" to start.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <?php 
                            $isSub = !empty($cat['parent_id']);
                            $thumb = $cat['banner_url'] ?: ($cat['icon_or_image_url'] ?: 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=120&q=80');
                        ?>
                        <tr id="cat-row-<?= $cat['id'] ?>" style="<?= $isSub ? 'background:#FDFCFF;' : '' ?>">
                            <td>
                                <img src="<?= e($thumb) ?>" alt="" style="width:48px;height:36px;object-fit:cover;border:1px solid #E4E0EC;background:#120F18;">
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <?php if ($isSub): ?>
                                        <span style="color:#9966cc;font-family:'Space Mono';font-weight:bold;margin-left:16px;">└─</span>
                                    <?php endif; ?>
                                    <strong style="font-size:13px;color:#0E0C14;"><?= e($cat['name']) ?></strong>
                                </div>
                                <?php if (!empty($cat['description'])): ?>
                                    <div style="font-size:11px;color:#8C869B;margin-top:2px;max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;<?= $isSub ? 'margin-left:36px;' : '' ?>">
                                        <?= e($cat['description']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code style="font-family:'Space Mono';font-size:11px;color:#4e2a84;background:#F4F2F8;padding:2px 6px;">
                                    <?= e($cat['slug']) ?>
                                </code>
                            </td>
                            <td>
                                <?php if ($isSub): ?>
                                    <span style="font-size:12px;font-weight:600;color:#231E2C;">
                                        <?= e($cat['parent_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="font-family:'Space Mono';font-size:10px;color:#8C869B;letter-spacing:0.05em;">
                                        — TOP LEVEL
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <span style="font-family:'Space Mono';font-size:11px;font-weight:700;background:#F4F2F8;padding:2px 7px;">
                                    <?= (int)$cat['product_count'] ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <div style="display:inline-flex;align-items:center;gap:4px;">
                                    <input type="number" class="cat-sort-input" data-id="<?= $cat['id'] ?>" value="<?= (int)$cat['sort_order'] ?>" 
                                           style="width:50px;padding:4px;font-family:'Space Mono';font-size:12px;text-align:center;border:1px solid #D1CADB;border-radius:0;">
                                    <button type="button" class="btn-save-order btn-admin btn-admin-outline btn-admin-sm" data-id="<?= $cat['id'] ?>" title="Save sort order" style="padding:4px 6px;">
                                        &check;
                                    </button>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <button type="button" class="btn-toggle-featured btn-admin btn-admin-sm <?= $cat['is_featured'] ? 'btn-admin-amethyst' : 'btn-admin-outline' ?>" 
                                        data-id="<?= $cat['id'] ?>" style="min-width:70px;">
                                    <?= $cat['is_featured'] ? 'HOMEPAGE' : 'HIDDEN' ?>
                                </button>
                            </td>
                            <td style="text-align:center;">
                                <button type="button" class="btn-toggle-active btn-admin btn-admin-sm <?= $cat['is_active'] ? 'btn-admin-outline' : '' ?>" 
                                        data-id="<?= $cat['id'] ?>" style="min-width:65px;<?= $cat['is_active'] ? 'color:#065F46;border-color:#A7F3D0;background:#D1FAE5;' : 'color:#991B1B;border-color:#FECACA;background:#FEE2E2;' ?>">
                                    <?= $cat['is_active'] ? 'ACTIVE' : 'OFF' ?>
                                </button>
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex;gap:6px;">
                                    <button type="button" class="btn-edit-category btn-admin btn-admin-outline btn-admin-sm" 
                                            data-category='<?= htmlspecialchars(json_encode($cat), ENT_QUOTES, 'UTF-8') ?>'>
                                        EDIT
                                    </button>
                                    <button type="button" class="btn-delete-category btn-admin btn-admin-outline btn-admin-sm" 
                                            data-id="<?= $cat['id'] ?>" data-name="<?= e($cat['name']) ?>" style="color:#EF4444;border-color:#FECACA;">
                                        &times;
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Category Drawer / Modal -->
<div id="category-drawer-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(11,9,16,0.65);z-index:900;justify-content:flex-end;">
    <div style="background:#FFFFFF;width:100%;max-width:580px;height:100%;overflow-y:auto;box-shadow:-4px 0 24px rgba(0,0,0,0.25);display:flex;flex-direction:column;">
        <!-- Drawer Header -->
        <div style="padding:22px 28px;background:#0E0C14;color:#FFFFFF;display:flex;align-items:center;justify-content:space-between;border-bottom:3px solid #4e2a84;">
            <div>
                <span style="font-family:'Space Mono';font-size:11px;color:#9966cc;letter-spacing:0.1em;text-transform:uppercase;">
                    CATEGORY SPECIFICATION
                </span>
                <h2 id="drawer-title" style="font-size:18px;margin:4px 0 0;font-weight:800;letter-spacing:0.04em;">
                    Create Category
                </h2>
            </div>
            <button type="button" id="btn-close-drawer" style="background:none;border:none;color:#A9A3B5;font-size:24px;cursor:pointer;padding:4px 8px;">
                &times;
            </button>
        </div>

        <!-- Form Body -->
        <div style="padding:28px;flex:1;">
            <div id="drawer-feedback" style="display:none;padding:12px;margin-bottom:16px;font-size:13px;"></div>

            <form id="form-category">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="cat-id" value="0">

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                        Category Name <span style="color:#EF4444;">*</span>
                    </label>
                    <input type="text" name="name" id="cat-name" placeholder="e.g. Graphic Streetwear, Heavyweight Hoodies" required
                           style="width:100%;padding:10px 12px;border:1px solid #D1CADB;font-size:13px;border-radius:0;box-sizing:border-box;">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                            Slug (URL Identifier)
                        </label>
                        <input type="text" name="slug" id="cat-slug" placeholder="e.g. graphic-streetwear"
                               style="width:100%;padding:10px 12px;border:1px solid #D1CADB;font-size:13px;border-radius:0;font-family:'Space Mono';box-sizing:border-box;">
                    </div>

                    <div>
                        <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                            Parent Category
                        </label>
                        <select name="parent_id" id="cat-parent-id" style="width:100%;padding:10px 12px;border:1px solid #D1CADB;font-size:13px;border-radius:0;background:#FFF;box-sizing:border-box;">
                            <option value="">-- TOP LEVEL (NO PARENT) --</option>
                            <?php foreach ($topLevelCategories as $top): ?>
                                <option value="<?= $top['id'] ?>"><?= e($top['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                        Description / Manifesto Note
                    </label>
                    <textarea name="description" id="cat-description" rows="3" placeholder="Category story, fabric highlights, and styling tips..."
                              style="width:100%;padding:10px 12px;border:1px solid #D1CADB;font-size:13px;border-radius:0;box-sizing:border-box;"></textarea>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                        Banner Image URL (Unsplash or Assets)
                    </label>
                    <input type="url" name="banner_url" id="cat-banner-url" placeholder="https://images.unsplash.com/..."
                           style="width:100%;padding:10px 12px;border:1px solid #D1CADB;font-size:13px;border-radius:0;box-sizing:border-box;">
                    <div style="margin-top:6px;display:flex;gap:6px;flex-wrap:wrap;">
                        <span style="font-size:11px;color:#8C869B;">Presets:</span>
                        <button type="button" class="btn-preset-img btn-admin btn-admin-outline btn-admin-sm" data-url="https://images.unsplash.com/photo-1558769132-cb1aea458c5e?auto=format&fit=crop&w=1200&q=80">Street</button>
                        <button type="button" class="btn-preset-img btn-admin btn-admin-outline btn-admin-sm" data-url="https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=1200&q=80">Dark Model</button>
                        <button type="button" class="btn-preset-img btn-admin btn-admin-outline btn-admin-sm" data-url="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1200&q=80">Couple</button>
                        <button type="button" class="btn-preset-img btn-admin btn-admin-outline btn-admin-sm" data-url="https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=1200&q=80">Apparel</button>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                            Icon / Square Thumbnail URL
                        </label>
                        <input type="url" name="icon_or_image_url" id="cat-icon-url" placeholder="Square image URL..."
                               style="width:100%;padding:10px 12px;border:1px solid #D1CADB;font-size:13px;border-radius:0;box-sizing:border-box;">
                    </div>

                    <div>
                        <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                            Sort Order (Priority)
                        </label>
                        <input type="number" name="sort_order" id="cat-sort-order" value="0"
                               style="width:100%;padding:10px 12px;border:1px solid #D1CADB;font-size:13px;border-radius:0;box-sizing:border-box;">
                    </div>
                </div>

                <div style="background:#FAF9FD;border:1px solid #E4E0EC;padding:14px;margin-bottom:16px;display:flex;flex-direction:column;gap:10px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                        <input type="checkbox" name="is_featured" id="cat-is-featured" value="1">
                        <strong>Show on Homepage Category Grid</strong>
                    </label>

                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
                        <input type="checkbox" name="is_active" id="cat-is-active" value="1" checked>
                        <span>Active Category (Visible to customers on Storefront)</span>
                    </label>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                        SEO Meta Title
                    </label>
                    <input type="text" name="meta_title" id="cat-meta-title" placeholder="e.g. Premium Oversized T-Shirts Sri Lanka"
                           style="width:100%;padding:10px 12px;border:1px solid #D1CADB;font-size:13px;border-radius:0;box-sizing:border-box;margin-bottom:10px;">

                    <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                        SEO Meta Description
                    </label>
                    <textarea name="meta_description" id="cat-meta-description" rows="2" placeholder="Search engine preview snippet..."
                              style="width:100%;padding:10px 12px;border:1px solid #D1CADB;font-size:13px;border-radius:0;box-sizing:border-box;"></textarea>
                </div>

                <button type="submit" id="btn-save-cat" class="btn-admin btn-admin-amethyst" style="width:100%;justify-content:center;padding:14px;font-size:12px;">
                    SAVE CATEGORY &rarr;
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const drawer = document.getElementById('category-drawer-overlay');
    const drawerTitle = document.getElementById('drawer-title');
    const drawerFeedback = document.getElementById('drawer-feedback');
    const form = document.getElementById('form-category');
    const btnAdd = document.getElementById('btn-add-category');
    const btnClose = document.getElementById('btn-close-drawer');
    const nameInput = document.getElementById('cat-name');
    const slugInput = document.getElementById('cat-slug');

    let isManualSlug = false;

    // Open for Create
    btnAdd.addEventListener('click', () => {
        form.reset();
        document.getElementById('cat-id').value = '0';
        document.getElementById('cat-sort-order').value = '<?= $totalCount + 1 ?>';
        document.getElementById('cat-is-active').checked = true;
        document.getElementById('cat-is-featured').checked = true;
        drawerTitle.textContent = 'Create Category';
        drawerFeedback.style.display = 'none';
        isManualSlug = false;
        drawer.style.display = 'flex';
    });

    // Close
    btnClose.addEventListener('click', () => drawer.style.display = 'none');
    drawer.addEventListener('click', (e) => {
        if (e.target === drawer) drawer.style.display = 'none';
    });

    // Auto-generate slug
    nameInput.addEventListener('input', () => {
        if (!isManualSlug) {
            slugInput.value = nameInput.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        }
    });

    slugInput.addEventListener('input', () => {
        isManualSlug = slugInput.value.trim().length > 0;
    });

    // Preset Image buttons
    document.querySelectorAll('.btn-preset-img').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('cat-banner-url').value = this.dataset.url;
        });
    });

    // Edit Category
    document.querySelectorAll('.btn-edit-category').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = JSON.parse(this.dataset.category);
            document.getElementById('cat-id').value = data.id;
            document.getElementById('cat-name').value = data.name || '';
            document.getElementById('cat-slug').value = data.slug || '';
            document.getElementById('cat-parent-id').value = data.parent_id || '';
            document.getElementById('cat-description').value = data.description || '';
            document.getElementById('cat-banner-url').value = data.banner_url || '';
            document.getElementById('cat-icon-url').value = data.icon_or_image_url || '';
            document.getElementById('cat-sort-order').value = data.sort_order || 0;
            document.getElementById('cat-is-featured').checked = data.is_featured == 1;
            document.getElementById('cat-is-active').checked = data.is_active == 1;
            document.getElementById('cat-meta-title').value = data.meta_title || '';
            document.getElementById('cat-meta-description').value = data.meta_description || '';

            drawerTitle.textContent = 'Edit: ' + data.name;
            drawerFeedback.style.display = 'none';
            isManualSlug = true;
            drawer.style.display = 'flex';
        });
    });

    // Form Submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-cat');
        btn.disabled = true;
        btn.textContent = 'SAVING...';
        drawerFeedback.style.display = 'none';

        const formData = new FormData(this);

        fetch('<?= base_url('ajax/admin_category.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'SAVE CATEGORY →';

            if (data.success) {
                showAdminToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 500);
            } else {
                drawerFeedback.style.background = '#FEE2E2';
                drawerFeedback.style.color = '#991B1B';
                drawerFeedback.textContent = data.message || 'Error saving category.';
                drawerFeedback.style.display = 'block';
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = 'SAVE CATEGORY →';
            drawerFeedback.style.background = '#FEE2E2';
            drawerFeedback.style.color = '#991B1B';
            drawerFeedback.textContent = 'Network error saving category.';
            drawerFeedback.style.display = 'block';
        });
    });

    // Delete Category
    document.querySelectorAll('.btn-delete-category').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;

            if (!confirm(`Are you sure you want to delete category "${name}"? This will unmap products.`)) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            fetch('<?= base_url('ajax/admin_category.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAdminToast(data.message, 'success');
                    const row = document.getElementById('cat-row-' + id);
                    if (row) row.remove();
                } else {
                    showAdminToast(data.message || 'Cannot delete category.', 'error');
                }
            })
            .catch(err => showAdminToast('Network error deleting category.', 'error'));
        });
    });

    // Toggle Featured
    document.querySelectorAll('.btn-toggle-featured').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const formData = new FormData();
            formData.append('action', 'toggle_featured');
            formData.append('id', id);

            fetch('<?= base_url('ajax/admin_category.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAdminToast(data.message, 'success');
                    if (data.is_featured) {
                        this.className = 'btn-toggle-featured btn-admin btn-admin-sm btn-admin-amethyst';
                        this.textContent = 'HOMEPAGE';
                    } else {
                        this.className = 'btn-toggle-featured btn-admin btn-admin-sm btn-admin-outline';
                        this.textContent = 'HIDDEN';
                    }
                }
            });
        });
    });

    // Toggle Active
    document.querySelectorAll('.btn-toggle-active').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const formData = new FormData();
            formData.append('action', 'toggle_active');
            formData.append('id', id);

            fetch('<?= base_url('ajax/admin_category.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAdminToast(data.message, 'success');
                    if (data.is_active) {
                        this.textContent = 'ACTIVE';
                        this.style.color = '#065F46';
                        this.style.borderColor = '#A7F3D0';
                        this.style.background = '#D1FAE5';
                    } else {
                        this.textContent = 'OFF';
                        this.style.color = '#991B1B';
                        this.style.borderColor = '#FECACA';
                        this.style.background = '#FEE2E2';
                    }
                }
            });
        });
    });

    // Save Sort Order
    document.querySelectorAll('.btn-save-order').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const input = document.querySelector(`.cat-sort-input[data-id="${id}"]`);
            const sortOrder = parseInt(input.value, 10) || 0;

            const formData = new FormData();
            formData.append('action', 'update_sort_order');
            formData.append('id', id);
            formData.append('sort_order', sortOrder);

            fetch('<?= base_url('ajax/admin_category.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAdminToast(data.message, 'success');
                }
            });
        });
    });
</script>
