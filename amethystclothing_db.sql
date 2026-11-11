-- ============================================================================
-- ENTERPRISE E-COMMERCE & CLOTHING BRAND DATABASE SCHEMA
-- Engine: InnoDB | Charset: utf8mb4_unicode_ci
-- Features Covered: 40+ (Guest Checkout, Phone Auth, Customer Merge, 
-- Multi-Variant, Auto-SKU, BEAST-style Specs, Mockup Engine, Bundles, 
-- Tiered Pricing, Multi-Warehouse, WhatsApp Recovery, Video Reviews, 
-- Exchanges, Loyalty Tiers, B2B Wholesale, CMS Builder, Audit Logs)
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- MODULE 1: SYSTEM SETTINGS, CURRENCIES, TAX & AUTO-UPDATES (WHITE-LABEL CORE)
-- ============================================================================

-- 1.1 Global Site Settings (වෙනත් කෙනෙක්ට සයිට් එක දෙද්දි Brand Name, Logo, Colors, Floating Button වෙනස් කරන්න)
CREATE TABLE `site_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_group` VARCHAR(50) NOT NULL COMMENT 'general, theme, whatsapp, meta_pixel, floating_btn',
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` LONGTEXT NULL,
    `is_public` TINYINT(1) DEFAULT 1 COMMENT '1 = Frontend එකට පෙන්විය හැකි, 0 = Private API Keys',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.2 Multi-Currency Support (LKR, USD, GBP, EUR, AUD)
CREATE TABLE `currencies` (
    `code` VARCHAR(3) PRIMARY KEY COMMENT 'LKR, USD, etc.',
    `symbol` VARCHAR(10) NOT NULL COMMENT 'Rs., $, £',
    `exchange_rate` DECIMAL(12, 6) NOT NULL DEFAULT 1.000000 COMMENT 'Base LKR = 1',
    `is_default` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.3 Automated Tax & Shipping Rates Engine
CREATE TABLE `tax_and_shipping_rates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `country_code` VARCHAR(2) DEFAULT 'LK',
    `region_or_district` VARCHAR(100) NOT NULL COMMENT 'Colombo, Suburbs, Outstation, International',
    `tax_name` VARCHAR(50) DEFAULT 'VAT',
    `tax_percentage` DECIMAL(5, 2) DEFAULT 0.00,
    `is_tax_inclusive` TINYINT(1) DEFAULT 1,
    `base_shipping_fee` DECIMAL(10, 2) NOT NULL DEFAULT 350.00,
    `free_shipping_threshold` DECIMAL(10, 2) NULL COMMENT 'මේ ගානට වඩා ගත්තොත් Free Delivery',
    `estimated_delivery_text` VARCHAR(150) DEFAULT '1-3 Working Days',
    `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.4 Software Updates & Schema Migrations Tracker
CREATE TABLE `schema_migrations` (
    `version` VARCHAR(50) PRIMARY KEY,
    `description` VARCHAR(255) NOT NULL,
    `installed_on` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- MODULE 2: ADMINS, CUSTOMERS, PHONE AUTH, MERGE, LOYALTY & B2B WHOLESALE
-- ============================================================================

-- 2.1 Admin & Staff Accounts (RBAC)
CREATE TABLE `admins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(150) NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('super_admin', 'manager', 'order_staff', 'warehouse_staff') DEFAULT 'super_admin',
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.2 Customers (Phone Number as Primary Identifier + Guest + B2B + Merge Support)
CREATE TABLE `customers` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `phone` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Primary Identifier (+947XXXXXXXX)',
    `secondary_phone` VARCHAR(20) NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NULL,
    `password_hash` VARCHAR(255) NULL COMMENT 'Null නම් Guest හෝ OTP පමණක් භාවිත කරන අයෙකි',
    `account_type` ENUM('guest', 'registered', 'b2b_wholesale') DEFAULT 'guest',
    `is_phone_verified` TINYINT(1) DEFAULT 0,
    
    -- Social Login IDs
    `google_id` VARCHAR(100) NULL UNIQUE,
    `facebook_id` VARCHAR(100) NULL UNIQUE,
    
    -- Loyalty & VIP Tier System
    `loyalty_tier` ENUM('bronze', 'silver', 'gold', 'amethyst_vip') DEFAULT 'bronze',
    `points_balance` INT UNSIGNED DEFAULT 0,
    `date_of_birth` DATE NULL COMMENT 'Birthday Discounts සඳහා',
    
    -- RFM Smart Segmentation Metrics
    `total_orders_count` INT UNSIGNED DEFAULT 0,
    `total_spent_amount` DECIMAL(12, 2) DEFAULT 0.00,
    `last_order_date` DATETIME NULL,
    `rfm_segment` ENUM('new_shopper', 'loyal', 'vip', 'at_risk', 'inactive') DEFAULT 'new_shopper',
    
    -- B2B Wholesale Specifics
    `company_name` VARCHAR(150) NULL,
    `b2b_discount_percentage` DECIMAL(5, 2) DEFAULT 0.00,
    
    -- Customer Merge Tracking (ඇඩ්මින් විසින් ප්‍රොෆයිල් දෙකක් Merge කළ විට)
    `is_merged` TINYINT(1) DEFAULT 0,
    `merged_into_customer_id` BIGINT UNSIGNED NULL,
    
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`merged_into_customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
    INDEX `idx_phone_lookup` (`phone`),
    INDEX `idx_segment` (`rfm_segment`, `loyalty_tier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.3 Customer Addresses
CREATE TABLE `customer_addresses` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `address_label` VARCHAR(50) DEFAULT 'Home' COMMENT 'Home, Office, Boarding',
    `recipient_name` VARCHAR(150) NOT NULL,
    `recipient_phone` VARCHAR(20) NOT NULL,
    `address_line1` VARCHAR(255) NOT NULL,
    `address_line2` VARCHAR(255) NULL,
    `city` VARCHAR(100) NOT NULL,
    `district` VARCHAR(100) NOT NULL,
    `postal_code` VARCHAR(20) NULL,
    `country_code` VARCHAR(2) DEFAULT 'LK',
    `is_default` TINYINT(1) DEFAULT 0,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.4 Customer Merge History Log
CREATE TABLE `customer_merge_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `source_customer_id` BIGINT UNSIGNED NOT NULL COMMENT 'අයින් වුණු පරණ ID එක',
    `target_customer_id` BIGINT UNSIGNED NOT NULL COMMENT 'එකතු වුණු ප්‍රධාන ID එක',
    `merged_by_admin_id` INT UNSIGNED NULL,
    `merged_data_snapshot` JSON NOT NULL COMMENT 'පරණ ප්‍රොෆයිල් එකේ තිබුණු දත්ත backup එකක් ලෙස',
    `merged_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`target_customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`merged_by_admin_id`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.5 Loyalty Points Ledger (ලකුණු එකතු වීම සහ කැපීම)
CREATE TABLE `loyalty_points_ledger` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `points` INT NOT NULL COMMENT '+ නම් එකතු වීමක්, - නම් වියදම් කිරීමක්',
    `reason` ENUM('order_earned', 'order_redeemed', 'first_order_bonus', 'birthday_bonus', 'admin_adjustment', 'merged_transfer') NOT NULL,
    `reference_order_id` BIGINT UNSIGNED NULL,
    `expires_at` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- MODULE 3: CATEGORIES, BRANDS & SIZE GUIDES
-- ============================================================================

-- 3.1 Brands (Multi-brand හෝ White-label සඳහා)
CREATE TABLE `brands` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `logo_url` VARCHAR(255) NULL,
    `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.2 Full Dynamic Categories & Subcategories (Bad Vibes, Couple, Oversized, etc.)
CREATE TABLE `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `parent_id` INT UNSIGNED NULL COMMENT 'Subcategory එකක් නම් ප්‍රධාන Category ID එක',
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `icon_or_image_url` VARCHAR(255) NULL,
    `banner_url` VARCHAR(255) NULL,
    `sort_order` INT DEFAULT 0,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `meta_title` VARCHAR(150) NULL,
    `meta_description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_parent_sort` (`parent_id`, `sort_order`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.3 Size & Fit Guide Templates (Regular Fit, Oversized, Henley, Kids)
CREATE TABLE `size_charts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(100) NOT NULL COMMENT 'උදා: Mens Oversized Fit Guide',
    `fit_type` VARCHAR(100) NULL COMMENT 'Regular, Oversized, Slim, Cropped',
    `measurements_json` JSON NOT NULL COMMENT '[{"size":"S", "chest_inch":38, "length_inch":27, "shoulder_inch":17}, ...]',
    `how_to_measure_image` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- MODULE 4: PRODUCTS, BEAST-STYLE SPECS, VARIANTS & AUTO-SKU
-- ============================================================================

-- 4.1 Core Products Table (with AI / Full-Text Search Index)
CREATE TABLE `products` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `brand_id` INT UNSIGNED NULL,
    `size_chart_id` INT UNSIGNED NULL,
    `name` VARCHAR(200) NOT NULL COMMENT 'උදා: Sky Blue Henley Neck T Shirt',
    `slug` VARCHAR(220) NOT NULL UNIQUE,
    `base_sku_prefix` VARCHAR(50) NOT NULL UNIQUE COMMENT 'උදා: BSTHNSKY',
    `short_description` TEXT NULL,
    
    -- Pricing
    `base_price` DECIMAL(10, 2) NOT NULL COMMENT 'සාමාන්‍ය මිල (Strike-through price: Rs. 1790)',
    `sale_price` DECIMAL(10, 2) NULL COMMENT 'විකුණන අඩු කළ මිල (Rs. 1450)',
    `wholesale_price` DECIMAL(10, 2) NULL COMMENT 'B2B තොග මිල',
    `b2b_min_order_qty` INT UNSIGNED DEFAULT 10 COMMENT 'B2B අවම ඇණවුම් ප්‍රමාණය',
    
    -- UI Badges & Flags
    `badge_text` VARCHAR(30) NULL COMMENT 'New, Hot, Limited Drop, Bestseller',
    `is_customizable_mockup` TINYINT(1) DEFAULT 0 COMMENT '1 = Dynamic Mockup Generator එක වැඩ කරන භාණ්ඩයකි',
    `is_featured` TINYINT(1) DEFAULT 0,
    `status` ENUM('draft', 'active', 'archived') DEFAULT 'active',
    
    -- Aggregated Social Proof (Fast loading සඳහා)
    `average_rating` DECIMAL(3, 2) DEFAULT 0.00,
    `reviews_count` INT UNSIGNED DEFAULT 0,
    `total_sold_count` INT UNSIGNED DEFAULT 0,
    
    -- Search & SEO
    `search_keywords` TEXT NULL COMMENT 'Fuzzy / Predictive search සඳහා අමතර වචන (Sinhala/Singlish/English)',
    `meta_title` VARCHAR(150) NULL,
    `meta_description` VARCHAR(255) NULL,
    
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`size_chart_id`) REFERENCES `size_charts`(`id`) ON DELETE SET NULL,
    INDEX `idx_status_featured` (`status`, `is_featured`),
    INDEX `idx_pricing` (`sale_price`, `base_price`),
    FULLTEXT KEY `ft_predictive_search` (`name`, `short_description`, `search_keywords`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.2 Product <-> Category Many-to-Many Pivot
CREATE TABLE `product_categories` (
    `product_id` BIGINT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`product_id`, `category_id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.3 BEAST-Style Product Specifications (තනි Product Page එකේ පෙන්වන විස්තර)
CREATE TABLE `product_specifications` (
    `product_id` BIGINT UNSIGNED PRIMARY KEY,
    `style_name` VARCHAR(100) NULL COMMENT 'උදා: Henley Neck T Shirt / Oversized Streetwear',
    `colour_label` VARCHAR(100) NULL COMMENT 'උදා: Sky Blue / Jet Black',
    `material` VARCHAR(150) DEFAULT '100% Combed Cotton' COMMENT 'උදා: 65/35 Cotton. Softened',
    `thickness_gsm` VARCHAR(50) DEFAULT '180 GSM' COMMENT 'උදා: 180 GSM / 220 GSM Heavyweight',
    `size_range_label` VARCHAR(50) DEFAULT 'XS – XXL',
    `quality_standards` VARCHAR(200) DEFAULT '100% QC Passed. Export Ready.',
    `specialities` VARCHAR(255) DEFAULT 'Tagless. Comfortable. Excellent Colorfastness. Anti-Shrink.',
    `warranty_info` VARCHAR(255) DEFAULT '14 Day Easy Returns & Size Exchanges.',
    `delivery_estimate` VARCHAR(255) DEFAULT 'Estimated 1-3 Working Days within Colombo & Suburbs. 3-5 Working Days Outstation.',
    `payment_options_text` VARCHAR(255) DEFAULT 'Card or Cash on Delivery at Checkout.',
    `origin_text` VARCHAR(100) DEFAULT 'A Genuine Product. Made in Sri Lanka.',
    `care_instructions` TEXT NULL COMMENT 'Wash cold, do not iron on print',
    `custom_attributes_json` JSON NULL COMMENT 'අමතර ඕනෑම spec එකක් දාන්න',
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.4 Multi-Layered Product Variants & Auto-SKU (Size, Color, Price Override, Stock)
CREATE TABLE `product_variants` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `sku` VARCHAR(80) NOT NULL UNIQUE COMMENT 'Auto-Generated: උදා BSTHNSKYL',
    `color_name` VARCHAR(50) NOT NULL COMMENT 'Sky Blue, Black',
    `color_hex` VARCHAR(10) DEFAULT '#000000' COMMENT 'UI Color Swatch එකට',
    `size_code` VARCHAR(20) NOT NULL COMMENT 'XS, S, M, L, XL, XXL, 3XL',
    `sort_order` INT DEFAULT 0,
    
    -- Variant Specific Pricing (3XL වලට ගාන වැඩිනම් හෝ වෙනම මිලක් නම්)
    `price_override` DECIMAL(10, 2) NULL COMMENT 'Null නම් product එකේ sale_price එක ගනී',
    `wholesale_price_override` DECIMAL(10, 2) NULL,
    
    -- Live Inventory & Progress Bar Support
    `total_stock_quantity` INT NOT NULL DEFAULT 0 COMMENT 'සියලුම ගබඩාවල එකතුව',
    `low_stock_threshold` INT DEFAULT 5 COMMENT 'මේ ගානට අඩු වුණාම Live Progress Bar එක පෙන්වයි',
    `weight_grams` INT DEFAULT 250 COMMENT 'Courier ගාස්තු ගණනයට',
    
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    INDEX `idx_filter_variants` (`product_id`, `color_name`, `size_code`, `total_stock_quantity`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.5 Product Media Gallery (Images & Short Videos)
CREATE TABLE `product_media` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `variant_id` BIGINT UNSIGNED NULL COMMENT 'විශේෂ පාටකට අදාළ පින්තූරයක් නම්',
    `media_type` ENUM('image', 'video') DEFAULT 'image',
    `url` VARCHAR(255) NOT NULL,
    `alt_text` VARCHAR(150) NULL,
    `is_primary` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- MODULE 5: DYNAMIC MOCKUP GENERATOR & CUSTOM CANVAS
-- ============================================================================

-- 5.1 Mockup Base Templates (කස්ටමර්ට පාට සහ ඩිසයින් දාලා බලන්න පුළුවන් Base T-Shirts)
CREATE TABLE `mockup_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `view_side` ENUM('front', 'back', 'left_sleeve', 'right_sleeve') DEFAULT 'front',
    `base_transparent_png_url` VARCHAR(255) NOT NULL COMMENT 'Shadows/creases තියෙන transparent ටී-ෂර්ට් පින්තූරය',
    `print_area_x` INT NOT NULL DEFAULT 150,
    `print_area_y` INT NOT NULL DEFAULT 120,
    `print_area_width` INT NOT NULL DEFAULT 300,
    `print_area_height` INT NOT NULL DEFAULT 400,
    `allowed_colors_json` JSON NOT NULL COMMENT '[{"name":"Jet Black","hex":"#111111"},{"name":"White","hex":"#FFFFFF"}]',
    `additional_print_cost` DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.2 Pre-made Graphic & Quote Designs Library (Bad Vibes Quotes, Gangster Graphics, etc.)
CREATE TABLE `mockup_designs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(100) NOT NULL COMMENT 'උදා: Zero Apologies / Money Talks',
    `design_category` VARCHAR(50) DEFAULT 'Quotes' COMMENT 'Bad Vibes, Luxury, Couple, Biker',
    `artwork_png_url` VARCHAR(255) NOT NULL,
    `preview_thumbnail_url` VARCHAR(255) NOT NULL,
    `extra_price` DECIMAL(10, 2) DEFAULT 0.00,
    `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- MODULE 6: MULTI-WAREHOUSE INVENTORY & STOCK-OUT ALERTS
-- ============================================================================

-- 6.1 Warehouses / Locations (Main Print Hub, Showroom, Secondary Hub)
CREATE TABLE `warehouses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `city` VARCHAR(100) NOT NULL,
    `address` TEXT NULL,
    `is_primary` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6.2 Warehouse Specific Stock Levels
CREATE TABLE `warehouse_inventory` (
    `warehouse_id` INT UNSIGNED NOT NULL,
    `variant_id` BIGINT UNSIGNED NOT NULL,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `reserved_quantity` INT NOT NULL DEFAULT 0 COMMENT 'Checkout එකේ යන අතරතුර lock වූ ප්‍රමාණය',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`warehouse_id`, `variant_id`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6.3 Out-of-Stock "Notify Me on WhatsApp" Subscriptions
CREATE TABLE `stock_notifications` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `variant_id` BIGINT UNSIGNED NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `is_notified` TINYINT(1) DEFAULT 0,
    `notified_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE,
    INDEX `idx_pending_notify` (`variant_id`, `is_notified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- MODULE 7: TIERED PRICING, BUNDLES, CROSS-SELLING & COUPONS
-- ============================================================================

-- 7.1 Tiered / Bulk Pricing Discounts (1ක් ගත්තොත් 2500, 3ක් ගත්තොත් 2000 ගානේ)
CREATE TABLE `product_discount_tiers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `min_quantity` INT UNSIGNED NOT NULL COMMENT 'අවම ප්‍රමාණය (උදා: 2, 3, 5)',
    `discount_type` ENUM('fixed_unit_price', 'percentage_off') DEFAULT 'fixed_unit_price',
    `discount_value` DECIMAL(10, 2) NOT NULL COMMENT 'ඒකකයක නව මිල හෝ වට්ටම් ප්‍රතිශතය',
    `badge_label` VARCHAR(50) NULL COMMENT 'උදා: Buy 3 Save 20%',
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    INDEX `idx_product_qty` (`product_id`, `min_quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7.2 Cross-Selling & Upselling Engine
CREATE TABLE `product_relations` (
    `product_id` BIGINT UNSIGNED NOT NULL,
    `related_product_id` BIGINT UNSIGNED NOT NULL,
    `relation_type` ENUM('upsell', 'cross_sell', 'frequently_bought_together') DEFAULT 'cross_sell',
    `sort_order` INT DEFAULT 0,
    PRIMARY KEY (`product_id`, `related_product_id`, `relation_type`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`related_product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7.3 Custom Bundle / Pack Deals (Pick any 3 T-Shirts for Rs. 4,990)
CREATE TABLE `bundle_deals` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL COMMENT 'උදා: Custom 3-Pack Streetwear Bundle',
    `slug` VARCHAR(160) NOT NULL UNIQUE,
    `required_items_count` INT UNSIGNED NOT NULL DEFAULT 3,
    `bundle_fixed_price` DECIMAL(10, 2) NOT NULL,
    `allowed_category_id` INT UNSIGNED NULL COMMENT 'මොන කැටගරියේ ටී-ෂර්ට් ද තෝරන්න පුළුවන්',
    `banner_url` VARCHAR(255) NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    FOREIGN KEY (`allowed_category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7.4 Custom Discount Coupons (Fixed or Percentage with "Up To" Max Cap)
CREATE TABLE `coupons` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE COMMENT 'උදා: AMETHYST10, BADDIE500',
    `description` VARCHAR(255) NULL,
    `discount_type` ENUM('fixed_amount', 'percentage', 'free_shipping') NOT NULL,
    `discount_value` DECIMAL(10, 2) NOT NULL COMMENT 'රුපියල් ගණන හෝ ප්‍රතිශතය (10%)',
    `max_discount_amount` DECIMAL(10, 2) NULL COMMENT 'Up to Rs. 1000 max cap (ප්‍රතිශත වට්ටම් සඳහා)',
    `min_order_value` DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'අවම බිල්පත් වටිනාකම',
    `usage_limit_total` INT UNSIGNED NULL COMMENT 'සම්පූර්ණ පාවිච්චි කළ හැකි වාර ගණන',
    `usage_limit_per_customer` INT UNSIGNED DEFAULT 1,
    `used_count` INT UNSIGNED DEFAULT 0,
    `is_first_order_only` TINYINT(1) DEFAULT 0,
    `valid_from` DATETIME NOT NULL,
    `valid_until` DATETIME NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    INDEX `idx_coupon_lookup` (`code`, `is_active`, `valid_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- MODULE 8: WISHLIST, CARTS & WHATSAPP ABANDONED CART RECOVERY
-- ============================================================================

-- 8.1 Wishlists
CREATE TABLE `wishlists` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BIGINT UNSIGNED NULL,
    `guest_session_token` VARCHAR(100) NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `variant_id` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8.2 Active & Abandoned Carts (WhatsApp Recovery සමඟ)
CREATE TABLE `carts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cart_token` VARCHAR(64) NOT NULL UNIQUE COMMENT 'Recovery link එකට පාවිච්චි වන token එක',
    `customer_id` BIGINT UNSIGNED NULL,
    `captured_phone` VARCHAR(20) NULL COMMENT 'Checkout එකේදී ටයිප් කරපු ගමන් auto-save වන අංකය',
    `captured_name` VARCHAR(150) NULL,
    `coupon_id` INT UNSIGNED NULL,
    `status` ENUM('active', 'abandoned', 'recovered', 'converted') DEFAULT 'active',
    `whatsapp_recovery_sent` TINYINT(1) DEFAULT 0,
    `whatsapp_recovery_sent_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE SET NULL,
    INDEX `idx_abandoned_lookup` (`status`, `whatsapp_recovery_sent`, `updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8.3 Cart Items
CREATE TABLE `cart_items` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cart_id` BIGINT UNSIGNED NOT NULL,
    `variant_id` BIGINT UNSIGNED NOT NULL,
    `bundle_id` INT UNSIGNED NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `custom_mockup_json` JSON NULL COMMENT 'Dynamic Mockup එකෙන් හදපු ඩිසයින් එකේ විස්තර සහ preview URL',
    FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- MODULE 9: ORDERS, REAL-TIME TRACKING & AUTOMATED INVOICING
-- ============================================================================

-- 9.1 Orders Master Table
CREATE TABLE `orders` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(30) NOT NULL UNIQUE COMMENT 'උදා: AME-2026-000105 (සර්ච් කරලා ට්‍රැක් කරන්න)',
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `warehouse_id` INT UNSIGNED NULL COMMENT 'භාණ්ඩය නිකුත් කරන ගබඩාව',
    
    -- Customer & Delivery Snapshot
    `customer_phone` VARCHAR(20) NOT NULL,
    `recipient_name` VARCHAR(150) NOT NULL,
    `shipping_address_line1` VARCHAR(255) NOT NULL,
    `shipping_address_line2` VARCHAR(255) NULL,
    `shipping_city` VARCHAR(100) NOT NULL,
    `shipping_district` VARCHAR(100) NOT NULL,
    `delivery_notes` VARCHAR(255) NULL,
    
    -- Financials & Multi-Currency
    `currency_code` VARCHAR(3) DEFAULT 'LKR',
    `exchange_rate_used` DECIMAL(12, 6) DEFAULT 1.000000,
    `subtotal_amount` DECIMAL(12, 2) NOT NULL,
    `discount_amount` DECIMAL(12, 2) DEFAULT 0.00,
    `coupon_code_used` VARCHAR(50) NULL,
    `loyalty_points_redeemed` INT UNSIGNED DEFAULT 0,
    `loyalty_discount_amount` DECIMAL(12, 2) DEFAULT 0.00,
    `tax_amount` DECIMAL(12, 2) DEFAULT 0.00,
    `shipping_fee` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `grand_total` DECIMAL(12, 2) NOT NULL,
    
    -- Payment & Order Status
    `payment_method` ENUM('cod', 'card_payhere', 'card_stripe', 'bank_transfer', 'koko_mintpay') DEFAULT 'cod',
    `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    `order_status` ENUM('placed', 'confirmed', 'printing_processing', 'packed', 'handed_to_courier', 'out_for_delivery', 'delivered', 'cancelled', 'returned') DEFAULT 'placed',
    
    -- Courier Tracking
    `courier_partner` VARCHAR(100) NULL COMMENT 'Prompt Xpress, Domex, Koombiyo, Certis',
    `courier_tracking_number` VARCHAR(100) NULL,
    
    `ordered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE SET NULL,
    INDEX `idx_order_tracking` (`order_number`, `customer_phone`),
    INDEX `idx_order_reporting` (`order_status`, `payment_status`, `ordered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9.2 Order Items
CREATE TABLE `order_items` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `variant_id` BIGINT UNSIGNED NOT NULL,
    `product_name_snapshot` VARCHAR(200) NOT NULL,
    `sku_snapshot` VARCHAR(80) NOT NULL,
    `color_snapshot` VARCHAR(50) NOT NULL,
    `size_snapshot` VARCHAR(20) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL,
    `unit_price` DECIMAL(10, 2) NOT NULL,
    `line_total` DECIMAL(12, 2) NOT NULL,
    `bundle_id` INT UNSIGNED NULL,
    `custom_mockup_data` JSON NULL COMMENT 'කස්ටම් මොකප් එකක් නම් print-ready artwork file URL එක',
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9.3 Real-Time Order Tracking Timeline History
CREATE TABLE `order_status_history` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL,
    `status` VARCHAR(50) NOT NULL,
    `public_comment` VARCHAR(255) NOT NULL COMMENT 'පාරිභෝගිකයාට Tracking bar එකේ පෙන්වන පණිවිඩය',
    `updated_by_admin_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9.4 Automated Invoices
CREATE TABLE `invoices` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` BIGINT UNSIGNED NOT NULL UNIQUE,
    `invoice_number` VARCHAR(40) NOT NULL UNIQUE COMMENT 'උදා: INV-2026-000105',
    `pdf_file_path` VARCHAR(255) NULL,
    `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- MODULE 10: VIDEO REVIEWS (UGC) & SIZE EXCHANGE MANAGEMENT
-- ============================================================================

-- 10.1 Customer Reviews & Short Video Product Reviews
CREATE TABLE `product_reviews` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `order_id` BIGINT UNSIGNED NULL COMMENT 'Verified Buyer ද කියලා බලන්න',
    `rating` TINYINT UNSIGNED NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `review_title` VARCHAR(150) NULL,
    `review_text` TEXT NULL,
    `video_url` VARCHAR(255) NULL COMMENT 'Short Video Review (MP4/WebM)',
    `images_json` JSON NULL COMMENT 'පාරිභෝගිකයා දාපු Photos array එකක්',
    `purchased_size` VARCHAR(20) NULL COMMENT 'උදා: Bought Size L - Fits True to Size',
    `is_verified_buyer` TINYINT(1) DEFAULT 1,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
    INDEX `idx_product_approved` (`product_id`, `status`, `rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10.2 Automated 14-Day Size Exchanges & Returns Management
CREATE TABLE `exchange_requests` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `exchange_number` VARCHAR(30) NOT NULL UNIQUE COMMENT 'උදා: EXC-2026-0012',
    `order_id` BIGINT UNSIGNED NOT NULL,
    `order_item_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `request_type` ENUM('size_exchange', 'color_exchange', 'defective_return') DEFAULT 'size_exchange',
    `old_variant_id` BIGINT UNSIGNED NOT NULL,
    `requested_new_variant_id` BIGINT UNSIGNED NULL COMMENT 'අලුතින් ඉල්ලන සයිස් එකේ Variant ID එක',
    `reason_notes` TEXT NULL,
    `proof_image_url` VARCHAR(255) NULL,
    `status` ENUM('requested', 'approved', 'pickup_scheduled', 'item_received_qc', 'replacement_dispatched', 'completed', 'rejected') DEFAULT 'requested',
    `replacement_order_id` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`order_item_id`) REFERENCES `order_items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`old_variant_id`) REFERENCES `product_variants`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`requested_new_variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- MODULE 11: LANDING PAGE BUILDER, WHATSAPP LOGS & AUDIT TRAIL
-- ============================================================================

-- 11.1 Dynamic Landing Page Builder (Seasonal Drops, Black Friday, Bad Vibes Drop)
CREATE TABLE `landing_pages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(160) NOT NULL UNIQUE,
    `page_blocks_json` LONGTEXT NOT NULL COMMENT 'Hero Banner, Countdown Timer, Featured Product Grid JSON',
    `countdown_ends_at` DATETIME NULL,
    `is_published` TINYINT(1) DEFAULT 1,
    `meta_title` VARCHAR(150) NULL,
    `meta_description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11.2 WhatsApp API Communication Logs (Order Updates, OTP, Cart Recovery)
CREATE TABLE `whatsapp_message_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `recipient_phone` VARCHAR(20) NOT NULL,
    `message_type` ENUM('order_confirmation', 'tracking_update', 'cart_recovery', 'stock_alert', 'otp_magic_link', 'promo') NOT NULL,
    `template_name` VARCHAR(100) NULL,
    `payload_sent` JSON NULL,
    `api_response_id` VARCHAR(150) NULL,
    `delivery_status` ENUM('queued', 'sent', 'delivered', 'read', 'failed') DEFAULT 'queued',
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_phone_msg` (`recipient_phone`, `message_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11.3 Admin Audit Log (ඇඩ්මින් පැනල් එකේ වෙන සියලුම වෙනස්කම්)
CREATE TABLE `admin_audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT UNSIGNED NULL,
    `action_type` VARCHAR(50) NOT NULL COMMENT 'create_product, update_price, merge_customer, change_order_status',
    `target_table` VARCHAR(50) NOT NULL,
    `target_record_id` BIGINT UNSIGNED NULL,
    `old_values_json` JSON NULL,
    `new_values_json` JSON NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE SET NULL,
    INDEX `idx_audit_search` (`target_table`, `target_record_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11.4 User Security & Login Activity Log
CREATE TABLE `user_activity_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `actor_type` ENUM('customer', 'admin') NOT NULL,
    `actor_id` BIGINT UNSIGNED NOT NULL,
    `event_name` VARCHAR(80) NOT NULL COMMENT 'login_success, login_failed, password_reset, profile_updated',
    `ip_address` VARCHAR(45) NOT NULL,
    `device_info` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_actor_lookup` (`actor_type`, `actor_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;