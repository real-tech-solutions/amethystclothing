<?php
/**
 * Amethystclothing.lk — Phase 5 Database Seeder
 * Seeds coupons, sample approved reviews, etc.
 */

require_once __DIR__ . '/../core/helpers.php';

echo "Seeding Phase 5 Data...\n";

// 1. Seed Coupons
try {
    $coupons = [
        [
            'code' => 'AMETHYST10',
            'description' => '10% Off Sitewide - Welcome Offer',
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'min_order_value' => 1000.00,
            'usage_limit_total' => 1000,
            'usage_limit_per_customer' => 5,
            'is_active' => 1
        ],
        [
            'code' => 'LUXURY500',
            'description' => 'Rs. 500 Off on orders above Rs. 4,000',
            'discount_type' => 'fixed_amount',
            'discount_value' => 500.00,
            'min_order_value' => 4000.00,
            'usage_limit_total' => 500,
            'usage_limit_per_customer' => 3,
            'is_active' => 1
        ]
    ];

    foreach ($coupons as $c) {
        $check = db()->prepare("SELECT id FROM coupons WHERE code = ?");
        $check->execute([$c['code']]);
        if (!$check->fetch()) {
            $stmt = db()->prepare("
                INSERT INTO coupons 
                (code, description, discount_type, discount_value, min_order_value, usage_limit_total, usage_limit_per_customer, used_count, is_first_order_only, valid_from, valid_until, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), ?)
            ");
            $stmt->execute([
                $c['code'],
                $c['description'],
                $c['discount_type'],
                $c['discount_value'],
                $c['min_order_value'],
                $c['usage_limit_total'],
                $c['usage_limit_per_customer'],
                $c['is_active']
            ]);
            echo "Inserted coupon: {$c['code']}\n";
        } else {
            echo "Coupon {$c['code']} already exists.\n";
        }
    }
} catch (Exception $e) {
    echo "Error seeding coupons: " . $e->getMessage() . "\n";
}

// 2. Seed a sample approved review for first product
try {
    $prod = db()->query("SELECT id FROM products LIMIT 1")->fetch();
    $cust = db()->query("SELECT id FROM customers LIMIT 1")->fetch();
    if ($prod && $cust) {
        $pId = (int)$prod['id'];
        $cId = (int)$cust['id'];
        $revCheck = db()->prepare("SELECT id FROM product_reviews WHERE product_id = ?");
        $revCheck->execute([$pId]);
        if (!$revCheck->fetch()) {
            $stmt = db()->prepare("
                INSERT INTO product_reviews 
                (product_id, customer_id, rating, review_title, review_text, purchased_size, is_verified_buyer, status, created_at)
                VALUES (?, ?, 5, 'Unmatched 240GSM Quality & Fit', 'The silhouette drops perfectly on the shoulders. Truly premium heavy cotton and the screen print shows zero cracking after two cold washes. Beast-tier streetwear for Sri Lanka.', 'L', 1, 'approved', NOW())
            ");
            $stmt->execute([$pId, $cId]);
            echo "Inserted sample review for product ID: {$pId}\n";
        }
    }
} catch (Exception $e) {
    echo "Error seeding review: " . $e->getMessage() . "\n";
}

echo "Phase 5 Seeding Complete.\n";
