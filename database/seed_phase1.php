<?php
/**
 * Phase 1 Database Seeder — Amethystclothing.lk
 * Run via CLI: C:\xampp\php\php.exe database/seed_phase1.php
 */

require_once __DIR__ . '/../core/helpers.php';

$pdo = db();
echo "=== Amethystclothing.lk — Phase 1 Seed ===\n\n";

// Disable foreign key checks for clean truncation/refresh if needed
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

// ── 1. SITE SETTINGS ──
echo "[1/8] Seeding site_settings...\n";
$settings = [
    ['general',   'brand_name',            'Amethystclothing.lk',  1],
    ['general',   'brand_display_name',    'AMETHYST',             1],
    ['general',   'slogan',                'Just own Your Style!', 1],
    ['general',   'base_currency',         'LKR',                  1],
    ['theme',     'primary_color',         '#4e2a84',              1],
    ['theme',     'secondary_color',       '#9966cc',              1],
    ['theme',     'dark_bg_color',         '#0B0910',              1],
    ['general',   'announcement_bar_text', 'ISLANDWIDE DELIVERY IN 1-3 WORKING DAYS | 14-DAY EASY SIZE EXCHANGES | CASH ON DELIVERY AVAILABLE', 1],
    ['whatsapp',  'whatsapp_number',       '+94770000000',         1],
    ['whatsapp',  'whatsapp_message',      'Hi AMETHYST! I need help with my order.',   1],
    ['general',   'contact_email',         'hello@amethystclothing.lk', 1],
    ['general',   'instagram_url',         'https://instagram.com/amethystclothing.lk', 1],
    ['general',   'facebook_url',          'https://facebook.com/amethystclothing.lk',  1],
    ['general',   'tiktok_url',            'https://tiktok.com/@amethystclothing.lk',   1],
];
$stmt = $pdo->prepare("INSERT INTO site_settings (setting_group, setting_key, setting_value, is_public) 
                       VALUES (?, ?, ?, ?) 
                       ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), is_public = VALUES(is_public)");
foreach ($settings as $s) {
    $stmt->execute($s);
}
echo "   -> " . count($settings) . " settings inserted.\n";

// ── 2. CURRENCIES ──
echo "[2/8] Seeding currencies...\n";
$pdo->exec("INSERT INTO currencies (code, symbol, exchange_rate, is_default, is_active) VALUES
    ('LKR', 'Rs.', 1.000000, 1, 1),
    ('USD', '$',  0.003300, 0, 1)
    ON DUPLICATE KEY UPDATE symbol = VALUES(symbol), exchange_rate = VALUES(exchange_rate), is_default = VALUES(is_default), is_active = VALUES(is_active)");
echo "   -> LKR (default) + USD inserted.\n";

// ── 3. TAX & SHIPPING ──
echo "[3/8] Seeding tax_and_shipping_rates...\n";
$pdo->exec("DELETE FROM tax_and_shipping_rates");
$pdo->exec("INSERT INTO tax_and_shipping_rates (country_code, region_or_district, tax_name, tax_percentage, is_tax_inclusive, base_shipping_fee, free_shipping_threshold, estimated_delivery_text, is_active) VALUES
    ('LK', 'Colombo & Suburbs', 'Included', 0.00, 1, 350.00, 7500.00, '1-3 Working Days', 1),
    ('LK', 'Outstation',        'Included', 0.00, 1, 450.00, 7500.00, '3-5 Working Days', 1)");
echo "   -> 2 shipping zones inserted.\n";

// ── 4. DEFAULT ADMIN ──
echo "[4/8] Seeding admins...\n";
$adminHash = password_hash('Admin@123', PASSWORD_BCRYPT);
$pdo->prepare("INSERT INTO admins (name, username, email, phone, password_hash, role, is_active) 
               VALUES (?, ?, ?, ?, ?, ?, ?) 
               ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), name = VALUES(name)")
    ->execute(['Super Admin', 'admin', 'admin@amethystclothing.lk', '+94771234567', $adminHash, 'super_admin', 1]);
echo "   -> Default admin (admin / Admin@123) created.\n";

// ── 5. BRAND ──
echo "[5/8] Seeding brands...\n";
$pdo->exec("INSERT INTO brands (name, slug, logo_url, is_active) 
            VALUES ('AMETHYST', 'amethyst', 'assets/images/logo_b-color.png', 1) 
            ON DUPLICATE KEY UPDATE name = VALUES(name), logo_url = VALUES(logo_url)");
echo "   -> AMETHYST brand inserted.\n";

// ── 6. SIZE CHARTS ──
echo "[6/8] Seeding size_charts...\n";
$regularFit = json_encode([
    ['size' => 'XS', 'chest_inch' => 36, 'length_inch' => 26, 'shoulder_inch' => 16],
    ['size' => 'S',  'chest_inch' => 38, 'length_inch' => 27, 'shoulder_inch' => 17],
    ['size' => 'M',  'chest_inch' => 40, 'length_inch' => 28, 'shoulder_inch' => 18],
    ['size' => 'L',  'chest_inch' => 42, 'length_inch' => 29, 'shoulder_inch' => 19],
    ['size' => 'XL', 'chest_inch' => 44, 'length_inch' => 30, 'shoulder_inch' => 20],
    ['size' => 'XXL','chest_inch' => 46, 'length_inch' => 31, 'shoulder_inch' => 21],
]);
$oversizedFit = json_encode([
    ['size' => 'XS', 'chest_inch' => 40, 'length_inch' => 28, 'shoulder_inch' => 20],
    ['size' => 'S',  'chest_inch' => 42, 'length_inch' => 29, 'shoulder_inch' => 21],
    ['size' => 'M',  'chest_inch' => 44, 'length_inch' => 30, 'shoulder_inch' => 22],
    ['size' => 'L',  'chest_inch' => 46, 'length_inch' => 31, 'shoulder_inch' => 23],
    ['size' => 'XL', 'chest_inch' => 48, 'length_inch' => 32, 'shoulder_inch' => 24],
    ['size' => 'XXL','chest_inch' => 50, 'length_inch' => 33, 'shoulder_inch' => 25],
]);

$regChart = $pdo->query("SELECT id FROM size_charts WHERE title = 'Amethyst Regular & Henley Fit Guide'")->fetchColumn();
if (!$regChart) {
    $stmt = $pdo->prepare("INSERT INTO size_charts (title, fit_type, measurements_json, notes) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Amethyst Regular & Henley Fit Guide', 'Regular', $regularFit, 'Classic comfortable fit. If between sizes, size up.']);
    $regularChartId = $pdo->lastInsertId();
} else {
    $regularChartId = $regChart;
}

$overChart = $pdo->query("SELECT id FROM size_charts WHERE title = 'Amethyst Heavyweight Oversized Fit Guide'")->fetchColumn();
if (!$overChart) {
    $stmt = $pdo->prepare("INSERT INTO size_charts (title, fit_type, measurements_json, notes) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Amethyst Heavyweight Oversized Fit Guide', 'Oversized', $oversizedFit, 'Relaxed drop-shoulder fit. True oversized — do not size up.']);
    $oversizedChartId = $pdo->lastInsertId();
} else {
    $oversizedChartId = $overChart;
}
echo "   -> 2 size charts ready (IDs: $regularChartId, $oversizedChartId).\n";

// ── 7. CATEGORIES ──
echo "[7/8] Seeding categories...\n";
$categories = [
    ['Baddie / Street Rebel', 'baddie',               'Dark street-vibe, bold graphic & quote tees that hit different.', 1],
    ['Couple Editions',       'couple-collections',    'Matching streetwear sets for duos who own their style together.', 2],
    ['Oversized Comfort',     'oversized-comfort',     '220 GSM heavyweight drop-shoulder tees. Comfort meets street.', 3],
    ['Luxury Mindset',        'luxury-mindset',        'Money talks — minimal high-end aesthetics for the self-made.', 4],
    ['Henley & Classics',     'henley-neck-t-shirts',  'Everyday essential fits. Timeless cuts, premium cotton.', 5],
];
$catStmt = $pdo->prepare("INSERT INTO categories (name, slug, description, sort_order, is_featured, is_active) 
                         VALUES (?, ?, ?, ?, 1, 1) 
                         ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), sort_order = VALUES(sort_order)");
$catIds = [];
foreach ($categories as $c) {
    $catStmt->execute([$c[0], $c[1], $c[2], $c[3]]);
    $catIds[$c[1]] = $pdo->query("SELECT id FROM categories WHERE slug = '{$c[1]}'")->fetchColumn();
}
echo "   -> " . count($categories) . " categories ready.\n";

// ── 8. PRODUCTS ──
echo "[8/8] Seeding products...\n";

$brandId = $pdo->query("SELECT id FROM brands WHERE slug = 'amethyst'")->fetchColumn();

$products = [
    [
        'name'        => 'Sky Blue Henley Neck T Shirt',
        'slug'        => 'sky-blue-henley-neck-t-shirt',
        'sku_prefix'  => 'AMTHNSKY',
        'base_price'  => 1790.00,
        'sale_price'  => 1450.00,
        'badge'       => 'Bestseller',
        'category'    => 'henley-neck-t-shirts',
        'size_chart'  => $regularChartId,
        'short_desc'  => 'Premium 180 GSM softened cotton Henley. The perfect everyday essential that blends classic style with street-ready comfort.',
        'search_kw'   => 'henley neck t shirt sky blue basic classic cotton',
        'specs' => [
            'style_name'       => 'Henley Neck T Shirt',
            'colour_label'     => 'Sky Blue',
            'material'         => '65/35 Cotton. Softened',
            'thickness_gsm'    => '180 GSM',
            'size_range_label' => 'XS – XXL',
            'specialities'     => 'Tagless. Comfortable. Excellent Colorfastness. Anti-Shrink.',
        ],
        'variants' => [
            ['Sky Blue', '#87CEEB', 'XS'], ['Sky Blue', '#87CEEB', 'S'],
            ['Sky Blue', '#87CEEB', 'M'],  ['Sky Blue', '#87CEEB', 'L'],
            ['Sky Blue', '#87CEEB', 'XL'], ['Sky Blue', '#87CEEB', 'XXL'],
        ],
    ],
    [
        'name'        => 'Zero Apologies Heavyweight Oversized Tee',
        'slug'        => 'zero-apologies-heavyweight-oversized-tee',
        'sku_prefix'  => 'AMTBADZRO',
        'base_price'  => 3490.00,
        'sale_price'  => 2890.00,
        'badge'       => 'Hot Drop',
        'category'    => 'baddie',
        'size_chart'  => $oversizedChartId,
        'short_desc'  => 'Zero apologies. 220 GSM heavyweight combed cotton. Bold back statement on jet black — built for those who refuse to blend in.',
        'search_kw'   => 'zero apologies oversized baddie streetwear black graphic tee heavyweight 220 gsm',
        'specs' => [
            'style_name'       => 'Heavyweight Oversized Streetwear Tee',
            'colour_label'     => 'Jet Black',
            'material'         => '100% Combed Cotton',
            'thickness_gsm'    => '220 GSM',
            'size_range_label' => 'XS – XXL',
            'specialities'     => 'Heavyweight Drop-Shoulder. Bold Back Print. Tagless. Anti-Shrink.',
        ],
        'variants' => [
            ['Jet Black', '#111111', 'XS'], ['Jet Black', '#111111', 'S'],
            ['Jet Black', '#111111', 'M'],  ['Jet Black', '#111111', 'L'],
            ['Jet Black', '#111111', 'XL'], ['Jet Black', '#111111', 'XXL'],
        ],
    ],
    [
        'name'        => 'Money Talks Luxury Edition Tee',
        'slug'        => 'money-talks-luxury-edition-tee',
        'sku_prefix'  => 'AMTLUXMNY',
        'base_price'  => 3290.00,
        'sale_price'  => 2690.00,
        'badge'       => 'Limited Drop',
        'category'    => 'luxury-mindset',
        'size_chart'  => $regularChartId,
        'short_desc'  => 'Money Talks. Premium 200 GSM edition in obsidian black with refined typography. Designed for the relentless and self-made.',
        'search_kw'   => 'money talks luxury premium black minimal mindset 200 gsm',
        'specs' => [
            'style_name'       => 'Luxury Minimal Edition Tee',
            'colour_label'     => 'Obsidian Black',
            'material'         => '100% Combed Cotton',
            'thickness_gsm'    => '200 GSM',
            'size_range_label' => 'XS – XXL',
            'specialities'     => 'High-Density Typography. Premium Hand-Feel. Tagless. Export Quality.',
        ],
        'variants' => [
            ['Obsidian Black', '#0B0910', 'XS'], ['Obsidian Black', '#0B0910', 'S'],
            ['Obsidian Black', '#0B0910', 'M'],  ['Obsidian Black', '#0B0910', 'L'],
            ['Obsidian Black', '#0B0910', 'XL'], ['Obsidian Black', '#0B0910', 'XXL'],
        ],
    ],
    [
        'name'        => 'King & Queen Monochrome Couple Set',
        'slug'        => 'king-queen-monochrome-couple-set',
        'sku_prefix'  => 'AMTCPLKQN',
        'base_price'  => 5490.00,
        'sale_price'  => 4500.00,
        'badge'       => 'Couple Pack',
        'category'    => 'couple-collections',
        'size_chart'  => $regularChartId,
        'short_desc'  => 'King & Queen matching duo set. High-contrast monochrome aesthetic on premium combed cotton. Two tees, one unified statement.',
        'search_kw'   => 'king queen couple set matching pair monochrome streetwear duo',
        'specs' => [
            'style_name'       => 'Couple Edition Matching Set (2 Tees)',
            'colour_label'     => 'Monochrome Black & White',
            'material'         => '100% Combed Cotton',
            'thickness_gsm'    => '180 GSM',
            'size_range_label' => 'XS – XXL',
            'specialities'     => 'Matching Set of 2 Tees. Architectural Typography. Tagless. Soft-washed.',
        ],
        'variants' => [
            ['Black (King)',  '#111111', 'XS'], ['Black (King)',  '#111111', 'S'],
            ['Black (King)',  '#111111', 'M'],  ['Black (King)',  '#111111', 'L'],
            ['Black (King)',  '#111111', 'XL'], ['Black (King)',  '#111111', 'XXL'],
            ['White (Queen)', '#FFFFFF', 'XS'], ['White (Queen)', '#FFFFFF', 'S'],
            ['White (Queen)', '#FFFFFF', 'M'],  ['White (Queen)', '#FFFFFF', 'L'],
            ['White (Queen)', '#FFFFFF', 'XL'], ['White (Queen)', '#FFFFFF', 'XXL'],
        ],
    ],
];

$prodStmt = $pdo->prepare("INSERT INTO products (brand_id, size_chart_id, name, slug, base_sku_prefix, short_description, base_price, sale_price, badge_text, is_featured, status, search_keywords) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'active', ?)
                          ON DUPLICATE KEY UPDATE name = VALUES(name), base_price = VALUES(base_price), sale_price = VALUES(sale_price), badge_text = VALUES(badge_text), short_description = VALUES(short_description)");

$specStmt = $pdo->prepare("INSERT INTO product_specifications (product_id, style_name, colour_label, material, thickness_gsm, size_range_label, specialities) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)
                          ON DUPLICATE KEY UPDATE style_name = VALUES(style_name), colour_label = VALUES(colour_label), material = VALUES(material), thickness_gsm = VALUES(thickness_gsm), specialities = VALUES(specialities)");

$varStmt  = $pdo->prepare("INSERT INTO product_variants (product_id, sku, color_name, color_hex, size_code, sort_order, total_stock_quantity, low_stock_threshold, weight_grams) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                          ON DUPLICATE KEY UPDATE color_name = VALUES(color_name), color_hex = VALUES(color_hex), total_stock_quantity = VALUES(total_stock_quantity)");

$pcStmt   = $pdo->prepare("INSERT IGNORE INTO product_categories (product_id, category_id) VALUES (?, ?)");

$sizeOrder = ['XS' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5, 'XXL' => 6];

foreach ($products as $p) {
    // Upsert product
    $prodStmt->execute([
        $brandId, $p['size_chart'], $p['name'], $p['slug'], $p['sku_prefix'],
        $p['short_desc'], $p['base_price'], $p['sale_price'], $p['badge'], $p['search_kw']
    ]);
    $productId = $pdo->query("SELECT id FROM products WHERE slug = '{$p['slug']}'")->fetchColumn();

    // Upsert specs
    $sp = $p['specs'];
    $specStmt->execute([
        $productId, $sp['style_name'], $sp['colour_label'], $sp['material'],
        $sp['thickness_gsm'], $sp['size_range_label'], $sp['specialities']
    ]);

    // Insert variants
    foreach ($p['variants'] as $v) {
        $sku = $p['sku_prefix'] . $v[2];
        $sort = $sizeOrder[$v[2]] ?? 0;
        $stock = 25;
        $weight = ($sp['thickness_gsm'] === '220 GSM') ? 320 : 250;
        $varStmt->execute([$productId, $sku, $v[0], $v[1], $v[2], $sort, $stock, 5, $weight]);
    }

    // Link category
    if (isset($catIds[$p['category']])) {
        $pcStmt->execute([$productId, $catIds[$p['category']]]);
    }

    echo "   -> Product: {$p['name']} (Base SKU: {$p['sku_prefix']}, " . count($p['variants']) . " variants ready)\n";
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

// ── SUMMARY ──
echo "\n=== SEED COMPLETE ===\n";
$tables = [
    'site_settings', 'currencies', 'tax_and_shipping_rates', 'admins',
    'brands', 'size_charts', 'categories', 'products',
    'product_specifications', 'product_variants', 'product_categories'
];
foreach ($tables as $t) {
    $c = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
    echo "  $t: $c rows\n";
}
echo "\nReady for Phase 1 verification.\n";
