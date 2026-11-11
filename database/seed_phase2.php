<?php
/**
 * Phase 2 Database Seeder — Amethystclothing.lk
 * Updates categories with background banners, populates product_media with high-res Unsplash apparel images,
 * and sets diverse stock levels in product_variants to thoroughly test the dynamic In Stock / Low Stock / Out of Stock logic.
 *
 * Run via CLI: C:\xampp\php\php.exe database/seed_phase2.php
 */

require_once __DIR__ . '/../core/helpers.php';

$pdo = db();
echo "=== Amethystclothing.lk — Phase 2 Seeder ===\n\n";

// ── 1. UPDATE CATEGORIES WITH BACKGROUND BANNERS ──
echo "[1/3] Updating categories with high-res Unsplash banners...\n";

$catBanners = [
    'baddie' => [
        'banner' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=1000&q=80',
        'icon'   => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=400&q=80',
    ],
    'couple-collections' => [
        'banner' => 'https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?auto=format&fit=crop&w=1000&q=80',
        'icon'   => 'https://images.unsplash.com/photo-1516589178581-6cd7833ae3b2?auto=format&fit=crop&w=400&q=80',
    ],
    'oversized-comfort' => [
        'banner' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=1000&q=80',
        'icon'   => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=400&q=80',
    ],
    'luxury-mindset' => [
        'banner' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=1000&q=80',
        'icon'   => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=400&q=80',
    ],
    'henley-neck-t-shirts' => [
        'banner' => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?auto=format&fit=crop&w=1000&q=80',
        'icon'   => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?auto=format&fit=crop&w=400&q=80',
    ],
];

$upCat = $pdo->prepare("UPDATE categories SET banner_url = ?, icon_or_image_url = ? WHERE slug = ?");
foreach ($catBanners as $slug => $data) {
    $upCat->execute([$data['banner'], $data['icon'], $slug]);
    echo "   -> Updated category: $slug\n";
}

// ── 2. SEED PRODUCT MEDIA (HIGH-RES APPAREL PHOTOS) ──
echo "\n[2/3] Seeding product_media gallery...\n";
$pdo->exec("DELETE FROM product_media");

$productMedia = [
    'sky-blue-henley-neck-t-shirt' => [
        [
            'url' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=800&h=1000&q=80',
            'alt' => 'Sky Blue Henley Neck T Shirt — Front View',
            'is_primary' => 1,
            'sort_order' => 1
        ],
        [
            'url' => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?auto=format&fit=crop&w=800&h=1000&q=80',
            'alt' => 'Sky Blue Henley Neck T Shirt — Detail & Texture',
            'is_primary' => 0,
            'sort_order' => 2
        ]
    ],
    'zero-apologies-heavyweight-oversized-tee' => [
        [
            'url' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=800&h=1000&q=80',
            'alt' => 'Zero Apologies Heavyweight Oversized Tee — Streetwear Cut',
            'is_primary' => 1,
            'sort_order' => 1
        ],
        [
            'url' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=800&h=1000&q=80',
            'alt' => 'Zero Apologies Heavyweight Oversized Tee — 220 GSM Fit',
            'is_primary' => 0,
            'sort_order' => 2
        ]
    ],
    'money-talks-luxury-edition-tee' => [
        [
            'url' => 'https://images.unsplash.com/photo-1503342394128-c104d54dba01?auto=format&fit=crop&w=800&h=1000&q=80',
            'alt' => 'Money Talks Luxury Edition Tee — Obsidian Minimalist',
            'is_primary' => 1,
            'sort_order' => 1
        ],
        [
            'url' => 'https://images.unsplash.com/photo-1527719327859-c6ce80353573?auto=format&fit=crop&w=800&h=1000&q=80',
            'alt' => 'Money Talks Luxury Edition Tee — Fabric & Collar Detail',
            'is_primary' => 0,
            'sort_order' => 2
        ]
    ],
    'king-queen-monochrome-couple-set' => [
        [
            'url' => 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=800&h=1000&q=80',
            'alt' => 'King & Queen Monochrome Couple Set — Duo Streetwear',
            'is_primary' => 1,
            'sort_order' => 1
        ],
        [
            'url' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=800&h=1000&q=80',
            'alt' => 'King & Queen Monochrome Couple Set — Lifestyle Fit',
            'is_primary' => 0,
            'sort_order' => 2
        ]
    ],
];

$mediaStmt = $pdo->prepare("INSERT INTO product_media (product_id, media_type, url, alt_text, is_primary, sort_order) VALUES (?, 'image', ?, ?, ?, ?)");
foreach ($productMedia as $slug => $items) {
    $pId = $pdo->query("SELECT id FROM products WHERE slug = '$slug'")->fetchColumn();
    if ($pId) {
        foreach ($items as $m) {
            $mediaStmt->execute([$pId, $m['url'], $m['alt'], $m['is_primary'], $m['sort_order']]);
        }
        echo "   -> Inserted " . count($items) . " media items for: $slug\n";
    }
}

// ── 3. DIVERSIFY VARIANT STOCK LEVELS FOR LIVE STOCK TESTING ──
echo "\n[3/3] Setting dynamic stock quantities on product_variants...\n";
/**
 * Test case stock configuration across sizes:
 * XS  -> 12 (In Stock, green badge)
 * S   -> 20 (In Stock, green badge)
 * M   -> 4  (Low Stock - Only 4 left, orange badge)
 * L   -> 0  (Out of Stock, red badge, disabled button)
 * XL  -> 15 (In Stock, green badge)
 * XXL -> 2  (Low Stock - Only 2 left, orange badge)
 */
$stockConfig = [
    'XS'  => 12,
    'S'   => 20,
    'M'   => 4,
    'L'   => 0,
    'XL'  => 15,
    'XXL' => 2,
];

$upStock = $pdo->prepare("UPDATE product_variants SET total_stock_quantity = ? WHERE size_code = ?");
foreach ($stockConfig as $size => $qty) {
    $upStock->execute([$qty, $size]);
    echo "   -> Size $size stock set to $qty units\n";
}

echo "\n=== PHASE 2 SEED COMPLETED SUCCESSFULLY ===\n";
