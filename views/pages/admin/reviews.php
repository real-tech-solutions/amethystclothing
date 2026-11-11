<?php
/**
 * Amethystclothing.lk — Admin Reviews Moderation
 * Sharp Minimalist Luxury UI — Strictly No AI Slop
 */

require_once __DIR__ . '/../../../core/helpers.php';
require_admin_auth();

$pageTitle = 'Customer Reviews // AMETHYST ADMIN';

// Filter
$statusFilter = trim($_GET['status'] ?? '');
$searchQuery  = trim($_GET['q'] ?? '');

$where = [];
$params = [];

if (!empty($statusFilter)) {
    $where[] = "pr.status = :status";
    $params['status'] = $statusFilter;
}

if (!empty($searchQuery)) {
    $where[] = "(p.name LIKE :q OR c.full_name LIKE :q OR pr.review_title LIKE :q OR pr.review_text LIKE :q)";
    $params['q'] = '%' . $searchQuery . '%';
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$reviews = [];
try {
    $sql = "
        SELECT pr.*, p.name as product_name, p.slug as product_slug, p.base_sku_prefix,
               c.full_name as customer_name, c.phone as customer_phone
        FROM product_reviews pr
        JOIN products p ON p.id = pr.product_id
        LEFT JOIN customers c ON c.id = pr.customer_id
        {$whereSql}
        ORDER BY pr.created_at DESC
    ";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $reviews = $stmt->fetchAll();
} catch (Exception $e) {
    $reviews = [];
}

// Counts for tabs
$pendingCount = (int)db()->query("SELECT COUNT(*) FROM product_reviews WHERE status = 'pending'")->fetchColumn();
$approvedCount = (int)db()->query("SELECT COUNT(*) FROM product_reviews WHERE status = 'approved'")->fetchColumn();
?>

<div class="admin-page-header">
    <div>
        <div class="admin-breadcrumb">COMMUNITY // SOCIAL PROOF & FEEDBACK</div>
        <h1 class="admin-heading">Customer Reviews Moderation</h1>
    </div>
    <div class="admin-header-actions">
        <a href="<?= base_url('admin/reviews') ?>" class="btn-admin btn-admin-outline btn-admin-sm">
            REFRESH REVIEWS
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="admin-card" style="padding: 18px 24px; margin-bottom: 24px;">
    <form method="GET" action="<?= base_url('admin/reviews') ?>" style="display:flex;flex-wrap:wrap;gap:14px;align-items:center;justify-content:space-between;">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
            <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="Search reviews, reviewer, product..." 
                   style="padding:9px 14px;border:1px solid #D1CADB;font-size:13px;width:240px;outline:none;border-radius:0;">
            
            <select name="status" style="padding:9px 14px;border:1px solid #D1CADB;font-size:13px;outline:none;background:#FFFFFF;border-radius:0;">
                <option value="">All Review Statuses</option>
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending Verification (<?= $pendingCount ?>)</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved (<?= $approvedCount ?>)</option>
                <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>

            <button type="submit" class="btn-admin btn-admin-sm">FILTER</button>
            <?php if (!empty($searchQuery) || !empty($statusFilter)): ?>
                <a href="<?= base_url('admin/reviews') ?>" class="btn-admin btn-admin-outline btn-admin-sm">RESET</a>
            <?php endif; ?>
        </div>

        <div style="font-family:'Space Mono';font-size:11px;color:#716B80;">
            TOTAL REVIEWS: <?= count($reviews) ?>
        </div>
    </form>
</div>

<!-- Reviews Table -->
<div class="admin-card">
    <div style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:12%;">Date</th>
                    <th style="width:20%;">Product</th>
                    <th style="width:18%;">Reviewer</th>
                    <th style="width:10%;">Rating</th>
                    <th style="width:25%;">Review Details</th>
                    <th style="width:15%;text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;padding:48px;color:#8C869B;">
                            No reviews found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                        <tr id="review-row-<?= $rev['id'] ?>">
                            <td style="font-size:11px;color:#6B6578;font-family:'Space Mono';">
                                <?= date('Y-m-d H:i', strtotime($rev['created_at'])) ?>
                            </td>
                            <td>
                                <div style="font-weight:700;font-size:13px;"><?= e($rev['product_name']) ?></div>
                                <div style="font-family:'Space Mono';font-size:10px;color:#716B80;">
                                    <?= e($rev['base_sku_prefix']) ?> &bull; <a href="<?= base_url('product/' . $rev['product_slug']) ?>" target="_blank" style="color:#9966cc;">View &nearr;</a>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:600;font-size:13px;"><?= e($rev['customer_name'] ?: 'Guest Customer') ?></div>
                                <div style="font-family:'Space Mono';font-size:11px;color:#716B80;"><?= e($rev['customer_phone'] ?? '') ?></div>
                                <?php if (!empty($rev['is_verified_buyer'])): ?>
                                    <span style="font-family:'Space Mono';font-size:9px;color:#065F46;background:#D1FAE5;padding:2px 6px;display:inline-block;margin-top:4px;">
                                        &check; VERIFIED BUYER
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="color:#F59E0B;font-size:14px;letter-spacing:1px;">
                                    <?= str_repeat('★', (int)$rev['rating']) ?><?= str_repeat('☆', 5 - (int)$rev['rating']) ?>
                                </div>
                                <span style="font-family:'Space Mono';font-size:10px;color:#716B80;">Size: <?= e($rev['purchased_size'] ?? 'M') ?></span>
                            </td>
                            <td>
                                <div style="font-weight:700;font-size:13px;margin-bottom:4px;"><?= e($rev['review_title']) ?></div>
                                <div style="font-size:12px;color:#454050;line-height:1.4;"><?= e($rev['review_text']) ?></div>
                                <div style="margin-top:6px;">
                                    <span class="admin-status-badge badge-<?= $rev['status'] === 'approved' ? 'instock' : ($rev['status'] === 'rejected' ? 'outofstock' : 'lowstock') ?>" id="status-badge-<?= $rev['id'] ?>">
                                        <?= strtoupper($rev['status']) ?>
                                    </span>
                                </div>
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex;flex-direction:column;gap:4px;align-items:flex-end;">
                                    <?php if ($rev['status'] !== 'approved'): ?>
                                        <button type="button" class="btn-admin btn-admin-amethyst btn-admin-sm btn-mod" data-id="<?= $rev['id'] ?>" data-action="approve">
                                            APPROVE
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($rev['status'] !== 'rejected'): ?>
                                        <button type="button" class="btn-admin btn-admin-outline btn-admin-sm btn-mod" data-id="<?= $rev['id'] ?>" data-action="reject" style="color:#EF4444;border-color:#FCA5A5;">
                                            REJECT
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn-mod" data-id="<?= $rev['id'] ?>" data-action="delete" style="background:none;border:none;font-family:'Space Mono';font-size:10px;color:#991B1B;cursor:pointer;padding:2px 4px;">
                                        Delete
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

<script>
    // Review Moderation AJAX
    document.querySelectorAll('.btn-mod').forEach(btn => {
        btn.addEventListener('click', function() {
            const revId = this.dataset.id;
            const action = this.dataset.action;

            if (action === 'delete' && !confirm('Are you sure you want to permanently delete this review?')) {
                return;
            }

            const formData = new FormData();
            formData.append('review_id', revId);
            formData.append('action', action);

            fetch('<?= base_url('ajax/moderate_review.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showAdminToast(data.message, 'success');
                    if (action === 'delete') {
                        const row = document.getElementById('review-row-' + revId);
                        if (row) row.remove();
                    } else {
                        // Reload to sync buttons and counts
                        setTimeout(() => window.location.reload(), 400);
                    }
                } else {
                    showAdminToast(data.message || 'Action failed', 'error');
                }
            })
            .catch(err => {
                showAdminToast('Network error moderating review', 'error');
            });
        });
    });
</script>
