<?php
/**
 * Categories Archive Page — Amethystclothing.lk
 * Phase 6.5: Full Category Catalog with Pagination & High-Impact Visuals
 */
$pageTitle = 'Collections & Categories Archive — Amethystclothing.lk';

$pdo = db();
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;

$totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1")->fetchColumn();
$totalPages = max(1, (int)ceil($totalCategories / $perPage));

$catStmt = $pdo->prepare("
    SELECT c.*, COUNT(DISTINCT pc.product_id) AS prod_count 
    FROM categories c 
    LEFT JOIN product_categories pc ON c.id = pc.category_id 
    WHERE c.is_active = 1 
    GROUP BY c.id 
    ORDER BY c.sort_order ASC, c.id ASC 
    LIMIT :limit OFFSET :offset
");
$catStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$catStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$catStmt->execute();
$categories = $catStmt->fetchAll();
?>
<section class="section" style="padding-top: 50px;">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav class="breadcrumb-nav">
            <a href="<?= base_url() ?>">HOME</a>
            <span>/</span>
            <strong>ALL CATEGORIES</strong>
        </nav>

        <!-- Section Header -->
        <div class="section-head" style="margin-bottom: 36px; border-bottom: 1px solid var(--border-crisp); padding-bottom: 24px;">
            <div class="section-head__left">
                <span class="section-eyebrow"><?= e(__('category_sec_tag')) ?> // FULL ARCHIVE</span>
                <h1 class="section-title"><?= e(__('category_sec_title')) ?></h1>
                <p class="section-subtitle">Browse through all active collections, silhouettes, and exclusive garment classifications.</p>
            </div>
            <div class="section-head__right">
                <span class="product-count-badge"><?= $totalCategories ?> COLLECTIONS</span>
            </div>
        </div>

        <!-- Categories Grid -->
        <div class="category-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 24px; margin-bottom: 48px;">
            <?php foreach ($categories as $idx => $cat): ?>
                <?php 
                    $catBg = !empty($cat['banner_url']) 
                        ? $cat['banner_url'] 
                        : 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=800&q=80';
                    $indexNum = str_pad((string)($offset + $idx + 1), 2, '0', STR_PAD_LEFT);
                ?>
                <a href="<?= base_url('category/' . $cat['slug']) ?>" 
                   class="category-card category-card--with-bg" 
                   style="min-height: 280px; background-image: linear-gradient(180deg, rgba(11, 9, 16, 0.45) 0%, rgba(11, 9, 16, 0.92) 100%), url('<?= e($catBg) ?>');">
                    <div class="category-card__header">
                        <span class="category-card__index"><?= $indexNum ?> //</span>
                        <span class="category-card__sku"><?= (int)$cat['prod_count'] ?> SILHOUETTES</span>
                    </div>
                    <div class="category-card__body">
                        <h3 class="category-card__title"><?= e($cat['name']) ?></h3>
                        <p class="category-card__desc"><?= e($cat['description']) ?></p>
                    </div>
                    <div class="category-card__footer">
                        <span><?= e(__('explore_category')) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Minimalist Pagination Component (0px border-radius, #4e2a84) -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-bar" style="display:flex; justify-content:center; align-items:center; gap:6px; margin-top:40px; padding-top:24px; border-top:1px solid var(--border-crisp);">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="pagination-btn" style="padding:8px 14px; font-family:var(--font-mono); font-size:11px; font-weight:700; border:1px solid var(--border-crisp); background:#FFF; color:var(--text-primary); text-decoration:none;">
                        &larr; PREV
                    </a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?page=<?= $p ?>" class="pagination-btn <?= $p === $page ? 'is-active' : '' ?>" 
                       style="padding:8px 14px; font-family:var(--font-mono); font-size:11px; font-weight:700; border:1px solid <?= $p === $page ? 'var(--brand-deep)' : 'var(--border-crisp)' ?>; background:<?= $p === $page ? 'var(--brand-deep)' : '#FFF' ?>; color:<?= $p === $page ? '#FFF' : 'var(--text-primary)' ?>; text-decoration:none;">
                        <?= str_pad((string)$p, 2, '0', STR_PAD_LEFT) ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="pagination-btn" style="padding:8px 14px; font-family:var(--font-mono); font-size:11px; font-weight:700; border:1px solid var(--border-crisp); background:#FFF; color:var(--text-primary); text-decoration:none;">
                        NEXT &rarr;
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
