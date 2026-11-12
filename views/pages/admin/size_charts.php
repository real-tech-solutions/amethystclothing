<?php
/**
 * Amethystclothing.lk — Admin Size & Fit Guides Management
 * Sharp Minimalist Luxury UI — Strictly No AI Slop
 */

require_once __DIR__ . '/../../../core/helpers.php';
require_admin_auth();

$pageTitle = 'Size & Fit Guides // AMETHYST ADMIN';

// Fetch all size charts with count of linked products
$sizeCharts = [];
try {
    $stmt = db()->query("
        SELECT sc.*, 
               (SELECT COUNT(*) FROM products p WHERE p.size_chart_id = sc.id) as linked_product_count
        FROM size_charts sc
        ORDER BY sc.id ASC
    ");
    $sizeCharts = $stmt->fetchAll();
} catch (Exception $e) {
    $sizeCharts = [];
}
?>

<div class="admin-page-header">
    <div>
        <div class="admin-breadcrumb">GARMENT SPECIFICATIONS // FIT ARCHITECTURE</div>
        <h1 class="admin-heading">Size & Fit Guides</h1>
    </div>
    <div class="admin-header-actions">
        <button type="button" class="btn-admin btn-admin-amethyst" id="btn-add-size-chart">
            &plus; CREATE SIZE GUIDE
        </button>
    </div>
</div>

<!-- Size Guides Table -->
<div class="admin-card">
    <div class="admin-card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h2 class="admin-card-title">Configured Garment Fit Specifications</h2>
        <span style="font-family:'Space Mono';font-size:11px;color:#716B80;">
            TOTAL GUIDES: <?= count($sizeCharts) ?>
        </span>
    </div>

    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:25%;">Guide Title</th>
                    <th style="width:15%;">Fit Type</th>
                    <th style="width:25%;">Size Range & Matrix</th>
                    <th style="width:12%;text-align:center;">Linked Products</th>
                    <th style="width:15%;text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sizeCharts)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;padding:48px;color:#8C869B;">
                            No size guides found. Click "+ CREATE SIZE GUIDE" to create one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($sizeCharts as $sc): ?>
                        <?php 
                            $measurements = json_decode($sc['measurements_json'], true) ?: [];
                            $sizesList = array_column($measurements, 'size');
                        ?>
                        <tr id="chart-row-<?= $sc['id'] ?>">
                            <td>
                                <strong style="font-size:14px;color:#0E0C14;"><?= e($sc['title']) ?></strong>
                                <?php if (!empty($sc['notes'])): ?>
                                    <div style="font-size:11px;color:#716B80;margin-top:2px;max-width:320px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        <?= e($sc['notes']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="admin-status-badge badge-printing_processing">
                                    <?= strtoupper(e($sc['fit_type'] ?: 'Regular')) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                    <?php foreach ($sizesList as $sz): ?>
                                        <span style="font-family:'Space Mono';font-size:11px;font-weight:700;background:#F4F2F8;padding:2px 6px;border:1px solid #E4E0EC;">
                                            <?= e($sz) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <span style="font-family:'Space Mono';font-size:11px;font-weight:700;background:#F4F2F8;padding:2px 8px;">
                                    <?= (int)$sc['linked_product_count'] ?> products
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex;gap:6px;">
                                    <button type="button" class="btn-edit-chart btn-admin btn-admin-outline btn-admin-sm" 
                                            data-id="<?= $sc['id'] ?>">
                                        EDIT
                                    </button>
                                    <button type="button" class="btn-delete-chart btn-admin btn-admin-outline btn-admin-sm" 
                                            data-id="<?= $sc['id'] ?>" data-title="<?= e($sc['title']) ?>" style="color:#EF4444;border-color:#FECACA;">
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

<!-- Size Chart Drawer / Modal -->
<div id="size-chart-drawer-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(11,9,16,0.65);z-index:900;justify-content:flex-end;">
    <div style="background:#FFFFFF;width:100%;max-width:640px;height:100%;overflow-y:auto;box-shadow:-4px 0 24px rgba(0,0,0,0.25);display:flex;flex-direction:column;">
        <!-- Header -->
        <div style="padding:22px 28px;background:#0E0C14;color:#FFFFFF;display:flex;align-items:center;justify-content:space-between;border-bottom:3px solid #4e2a84;">
            <div>
                <span style="font-family:'Space Mono';font-size:11px;color:#9966cc;letter-spacing:0.1em;text-transform:uppercase;">
                    FIT SPECIFICATION FILE
                </span>
                <h2 id="chart-drawer-title" style="font-size:18px;margin:4px 0 0;font-weight:800;letter-spacing:0.04em;">
                    Create Size Guide
                </h2>
            </div>
            <button type="button" id="btn-close-chart-drawer" style="background:none;border:none;color:#A9A3B5;font-size:24px;cursor:pointer;padding:4px 8px;">
                &times;
            </button>
        </div>

        <!-- Form Body -->
        <div style="padding:28px;flex:1;">
            <div id="chart-feedback" style="display:none;padding:12px;margin-bottom:16px;font-size:13px;"></div>

            <form id="form-size-chart">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="chart-id" value="0">

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                        Guide Title <span style="color:#EF4444;">*</span>
                    </label>
                    <input type="text" name="title" id="chart-title" placeholder="e.g. Heavyweight Oversized Fit Guide" required
                           style="width:100%;padding:10px 12px;border:1px solid #D1CADB;font-size:13px;border-radius:0;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                        Fit Type <span style="color:#EF4444;">*</span>
                    </label>
                    <select name="fit_type" id="chart-fit-type" style="width:100%;padding:10px 12px;border:1px solid #D1CADB;font-size:13px;border-radius:0;background:#FFF;box-sizing:border-box;">
                        <option value="Regular">Regular Fit (Standard Cut)</option>
                        <option value="Oversized">Oversized / Drop Shoulder Fit</option>
                        <option value="Relaxed">Relaxed Streetwear Fit</option>
                        <option value="Slim">Slim / Athletic Cut</option>
                    </select>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-family:'Space Mono';font-size:10px;text-transform:uppercase;color:#6B6578;margin-bottom:6px;">
                        Fit & Measuring Notes (Visible in Storefront Modal)
                    </label>
                    <textarea name="notes" id="chart-notes" rows="2" placeholder="e.g. Crafted with dropped shoulders for a relaxed streetwear aesthetic. True to size for intentional oversized drape."
                              style="width:100%;padding:10px 12px;border:1px solid #D1CADB;font-size:13px;border-radius:0;box-sizing:border-box;"></textarea>
                </div>

                <!-- Measurements Table Builder -->
                <div style="margin-bottom:24px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <label style="font-family:'Space Mono';font-size:11px;text-transform:uppercase;color:#0E0C14;font-weight:700;">
                            MEASUREMENTS MATRIX (INCHES)
                        </label>
                        <button type="button" id="btn-add-size-row" class="btn-admin btn-admin-outline btn-admin-sm">
                            &plus; ADD SIZE ROW
                        </button>
                    </div>

                    <table class="admin-table" style="border:1px solid #E4E0EC;">
                        <thead>
                            <tr style="background:#FAF9FD;">
                                <th style="width:25%;">Size</th>
                                <th style="width:25%;">Chest (In)</th>
                                <th style="width:25%;">Length (In)</th>
                                <th style="width:20%;">Shoulder (In)</th>
                                <th style="width:5%;text-align:center;"></th>
                            </tr>
                        </thead>
                        <tbody id="measurements-tbody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>

                <button type="submit" id="btn-save-chart" class="btn-admin btn-admin-amethyst" style="width:100%;justify-content:center;padding:14px;font-size:12px;">
                    SAVE SIZE GUIDE &rarr;
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const chartDrawer = document.getElementById('size-chart-drawer-overlay');
    const chartTitle = document.getElementById('chart-drawer-title');
    const chartFeedback = document.getElementById('chart-feedback');
    const chartForm = document.getElementById('form-size-chart');
    const btnAddChart = document.getElementById('btn-add-size-chart');
    const btnCloseChart = document.getElementById('btn-close-chart-drawer');
    const tbody = document.getElementById('measurements-tbody');

    const defaultRows = [
        { size: 'XS', chest_inch: 36, length_inch: 26, shoulder_inch: 16 },
        { size: 'S',  chest_inch: 38, length_inch: 27, shoulder_inch: 17 },
        { size: 'M',  chest_inch: 40, length_inch: 28, shoulder_inch: 18 },
        { size: 'L',  chest_inch: 42, length_inch: 29, shoulder_inch: 19 },
        { size: 'XL', chest_inch: 44, length_inch: 30, shoulder_inch: 20 },
        { size: 'XXL',chest_inch: 46, length_inch: 31, shoulder_inch: 21 },
        { size: '3XL',chest_inch: 48, length_inch: 32, shoulder_inch: 22 }
    ];

    function renderRows(rows) {
        tbody.innerHTML = '';
        rows.forEach((r, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <input type="text" class="row-size" value="${r.size || ''}" required 
                           style="width:100%;padding:6px 8px;font-family:'Space Mono';font-size:12px;font-weight:700;border:1px solid #D1CADB;border-radius:0;box-sizing:border-box;">
                </td>
                <td>
                    <input type="number" step="0.5" class="row-chest" value="${r.chest_inch || ''}" required 
                           style="width:100%;padding:6px 8px;font-family:'Space Mono';font-size:12px;border:1px solid #D1CADB;border-radius:0;box-sizing:border-box;">
                </td>
                <td>
                    <input type="number" step="0.5" class="row-length" value="${r.length_inch || ''}" required 
                           style="width:100%;padding:6px 8px;font-family:'Space Mono';font-size:12px;border:1px solid #D1CADB;border-radius:0;box-sizing:border-box;">
                </td>
                <td>
                    <input type="number" step="0.5" class="row-shoulder" value="${r.shoulder_inch || ''}" required 
                           style="width:100%;padding:6px 8px;font-family:'Space Mono';font-size:12px;border:1px solid #D1CADB;border-radius:0;box-sizing:border-box;">
                </td>
                <td style="text-align:center;">
                    <button type="button" class="btn-del-row" style="background:none;border:none;color:#EF4444;font-size:16px;cursor:pointer;padding:2px 6px;">&times;</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Attach delete row handlers
        tbody.querySelectorAll('.btn-del-row').forEach(b => {
            b.addEventListener('click', function() {
                if (tbody.querySelectorAll('tr').length > 1) {
                    this.closest('tr').remove();
                } else {
                    alert('At least one size row is required.');
                }
            });
        });
    }

    // Add Row Button
    document.getElementById('btn-add-size-row').addEventListener('click', () => {
        const lastRow = tbody.querySelector('tr:last-child');
        const defaultSize = lastRow ? 'NEW' : 'M';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="text" class="row-size" value="${defaultSize}" required style="width:100%;padding:6px 8px;font-family:'Space Mono';font-size:12px;font-weight:700;border:1px solid #D1CADB;border-radius:0;box-sizing:border-box;"></td>
            <td><input type="number" step="0.5" class="row-chest" value="40" required style="width:100%;padding:6px 8px;font-family:'Space Mono';font-size:12px;border:1px solid #D1CADB;border-radius:0;box-sizing:border-box;"></td>
            <td><input type="number" step="0.5" class="row-length" value="28" required style="width:100%;padding:6px 8px;font-family:'Space Mono';font-size:12px;border:1px solid #D1CADB;border-radius:0;box-sizing:border-box;"></td>
            <td><input type="number" step="0.5" class="row-shoulder" value="18" required style="width:100%;padding:6px 8px;font-family:'Space Mono';font-size:12px;border:1px solid #D1CADB;border-radius:0;box-sizing:border-box;"></td>
            <td style="text-align:center;"><button type="button" class="btn-del-row" style="background:none;border:none;color:#EF4444;font-size:16px;cursor:pointer;padding:2px 6px;">&times;</button></td>
        `;
        tbody.appendChild(tr);
        tr.querySelector('.btn-del-row').addEventListener('click', function() {
            tr.remove();
        });
    });

    // Open for Create
    btnAddChart.addEventListener('click', () => {
        chartForm.reset();
        document.getElementById('chart-id').value = '0';
        chartTitle.textContent = 'Create Size Guide';
        chartFeedback.style.display = 'none';
        renderRows(defaultRows);
        chartDrawer.style.display = 'flex';
    });

    // Close
    btnCloseChart.addEventListener('click', () => chartDrawer.style.display = 'none');
    chartDrawer.addEventListener('click', (e) => {
        if (e.target === chartDrawer) chartDrawer.style.display = 'none';
    });

    // Edit Size Chart
    document.querySelectorAll('.btn-edit-chart').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const formData = new FormData();
            formData.append('action', 'get');
            formData.append('id', id);

            fetch('<?= base_url('ajax/admin_size_chart.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.chart) {
                    const c = data.chart;
                    document.getElementById('chart-id').value = c.id;
                    document.getElementById('chart-title').value = c.title || '';
                    document.getElementById('chart-fit-type').value = c.fit_type || 'Regular';
                    document.getElementById('chart-notes').value = c.notes || '';
                    chartTitle.textContent = 'Edit: ' + c.title;
                    chartFeedback.style.display = 'none';
                    renderRows(c.measurements.length ? c.measurements : defaultRows);
                    chartDrawer.style.display = 'flex';
                }
            });
        });
    });

    // Submit Size Chart
    chartForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-chart');
        btn.disabled = true;
        btn.textContent = 'SAVING GUIDE...';
        chartFeedback.style.display = 'none';

        // Collect rows
        const rows = [];
        tbody.querySelectorAll('tr').forEach(tr => {
            const size = tr.querySelector('.row-size').value.trim();
            const chest = parseFloat(tr.querySelector('.row-chest').value) || 0;
            const length = parseFloat(tr.querySelector('.row-length').value) || 0;
            const shoulder = parseFloat(tr.querySelector('.row-shoulder').value) || 0;
            if (size) {
                rows.push({ size, chest_inch: chest, length_inch: length, shoulder_inch: shoulder });
            }
        });

        const formData = new FormData(this);
        formData.append('measurements_json', JSON.stringify(rows));

        fetch('<?= base_url('ajax/admin_size_chart.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'SAVE SIZE GUIDE →';

            if (data.success) {
                showAdminToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 500);
            } else {
                chartFeedback.style.background = '#FEE2E2';
                chartFeedback.style.color = '#991B1B';
                chartFeedback.textContent = data.message || 'Error saving size chart.';
                chartFeedback.style.display = 'block';
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.textContent = 'SAVE SIZE GUIDE →';
            chartFeedback.style.background = '#FEE2E2';
            chartFeedback.style.color = '#991B1B';
            chartFeedback.textContent = 'Network error saving size chart.';
            chartFeedback.style.display = 'block';
        });
    });

    // Delete Size Chart
    document.querySelectorAll('.btn-delete-chart').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const title = this.dataset.title;

            if (!confirm(`Are you sure you want to delete size guide "${title}"?`)) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);

            fetch('<?= base_url('ajax/admin_size_chart.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAdminToast(data.message, 'success');
                    const row = document.getElementById('chart-row-' + id);
                    if (row) row.remove();
                } else {
                    showAdminToast(data.message || 'Error deleting size chart.', 'error');
                }
            });
        });
    });
</script>
