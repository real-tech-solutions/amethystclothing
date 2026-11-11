<?php
/**
 * Localization Engine — Amethystclothing.lk
 * Bilingual Support: English (en) & Sinhala (si)
 */

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'si'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

function current_lang(): string
{
    return $_SESSION['lang'] ?? 'en';
}

function lang_strings(): array
{
    static $strings = null;
    if ($strings !== null) return $strings;

    $strings = [
        'en' => [
            // Header & Utility
            'slogan'              => 'Just own Your Style!',
            'announcement'        => 'ISLANDWIDE DELIVERY IN 1-3 WORKING DAYS | 14-DAY EASY SIZE EXCHANGES | CASH ON DELIVERY',
            'track_order'         => 'Track Order',
            'currency'            => 'CURRENCY',
            'language'            => 'LANG',
            'search_placeholder'  => 'SEARCH T-SHIRTS, OVERSIZED, HENLEYS, SKUS...',
            'bag'                 => 'BAG',
            'your_bag'            => 'YOUR BAG',
            'empty_bag'           => 'Your bag is empty.',
            'continue_shopping'   => 'CONTINUE SHOPPING',
            'track_order_modal_title' => 'TRACK YOUR ORDER',
            'order_number_label'  => 'ORDER NUMBER OR PHONE',
            'track_btn'           => 'TRACK',

            // Navigation
            'nav_shop_all'        => 'SHOP ALL',
            'nav_baddie'          => 'BADDIE',
            'nav_oversized'       => 'OVERSIZED',
            'nav_luxury'          => 'LUXURY MINDSET',
            'nav_couple'          => 'COUPLE',
            'nav_henley'          => 'HENLEY',

            // Hero Slider
            'hero_slide1_badge'   => 'PREMIUM PRINTING & APPAREL',
            'hero_slide1_title'   => 'JUST OWN YOUR STYLE!',
            'hero_slide1_sub'     => 'We specialize in Premium T-Shirt Printing and exclusive custom apparel. Crafted with 180–220 GSM combed cotton, bold graphics, and sharp streetwear fits.',
            'hero_slide2_badge'   => 'HEAVYWEIGHT STREETWEAR',
            'hero_slide2_title'   => '220 GSM OVERSIZED COLLECTION',
            'hero_slide2_sub'     => 'Relaxed drop-shoulder silhouettes engineered with luxury weight combed cotton and ultra-durable screen prints.',
            'hero_slide3_badge'   => 'MATCHING ESSENTIALS',
            'hero_slide3_title'   => 'COUPLE & CLASSIC EDITIONS',
            'hero_slide3_sub'     => 'Minimalist monochrome sets and everyday softened Henley tees made to elevate your daily style.',
            'explore_collection'  => 'EXPLORE COLLECTION',
            'view_oversized'      => '220 GSM OVERSIZED',
            'custom_printing'     => 'CUSTOM PRINTING',

            // Value Props
            'delivery_title'      => 'ISLANDWIDE DELIVERY',
            'delivery_desc'       => '1–3 Days Colombo & Suburbs. 3–5 Days Outstation.',
            'exchange_title'      => '14-DAY SIZE EXCHANGE',
            'exchange_desc'       => 'Zero friction. Seamless doorstep size exchange service.',
            'cod_title'           => 'CASH ON DELIVERY & KOKO',
            'cod_desc'            => 'Pay cash at doorstep or split into 3 with Koko / Mintpay.',
            'cotton_title'        => '100% COMBED COTTON',
            'cotton_desc'         => 'Anti-shrink, colorfast, export-grade tailored durability.',

            // Products & Catalog
            'new_arrivals_tag'    => 'LATEST RELEASES',
            'new_arrivals_title'  => 'FEATURED T-SHIRTS & STREETWEAR',
            'view_all_arrow'      => 'VIEW ALL PRODUCTS →',
            'view_details'        => 'VIEW FIT',
            'spec_material'       => 'Combed Cotton',

            // Categories
            'category_sec_tag'    => 'EXPLORE OUR DESIGNS',
            'category_sec_title'  => 'SHOP BY CATEGORY',
            'explore_category'    => 'EXPLORE CATEGORY →',

            // Single Product Page
            'select_size'         => 'SELECT SIZE:',
            'in_stock'            => 'IN STOCK',
            'low_stock'           => 'LOW STOCK — ONLY {count} LEFT',
            'out_of_stock'        => 'OUT OF STOCK',
            'add_to_bag'          => 'ADD TO BAG',
            'added_to_bag'        => 'ADDED TO BAG',
            'buy_now_guest'       => 'BUY NOW VIA GUEST CHECKOUT',
            'tech_specs_title'    => 'GARMENT SPECIFICATIONS // TECH SHEET',
            'material_label'      => 'MATERIAL',
            'gsm_label'           => 'GSM / WEIGHT',
            'colour_label'        => 'COLOUR',
            'fit_label'           => 'FIT TYPE',
            'standards_label'     => 'STANDARDS',
            'specialities_label'  => 'SPECIALITIES',
            'exchange_label'      => 'EXCHANGE',
            'delivery_label'      => 'DELIVERY',

            // Footer
            'footer_about'        => 'Amethyst is a premier Sri Lankan clothing brand specializing in premium T-shirt printing, heavyweight streetwear, and custom apparel. Built for those who own their style without apologies.',
            'footer_categories'   => 'COLLECTIONS',
            'footer_help'         => 'CUSTOMER CARE',
            'footer_contact'      => 'CONTACT US',
            'footer_follow'       => 'FOLLOW US',
            'footer_rights'       => 'ALL RIGHTS RESERVED.',
        ],
        'si' => [
            // Header & Utility
            'slogan'              => 'ඔබේම විලාසිතාවකින් බැබලෙන්න!',
            'announcement'        => 'දින 1-3 කින් මුළු දිවයිනටම බෙදාහැරීම | දින 14 ක පහසු ප්‍රමාණ හුවමාරුව | භාණ්ඩ ලැබුණු පසු මුදල් ගෙවීම්',
            'track_order'         => 'ඕඩරය සොයන්න',
            'currency'            => 'මුදල් වර්ගය',
            'language'            => 'භාෂාව',
            'search_placeholder'  => 'ටී-ෂර්ට්, ඕවර්සයිස්, හෙන්ලි, කෝඩ් සර්ච් කරන්න...',
            'bag'                 => 'බෑගය',
            'your_bag'            => 'ඔබගේ බෑගය',
            'empty_bag'           => 'ඔබගේ බෑගය හිස්ය.',
            'continue_shopping'   => 'සාප්පු සවාරියට',
            'track_order_modal_title' => 'ඔබගේ ඕඩරය සොයන්න',
            'order_number_label'  => 'ඕඩර් අංකය හෝ දුරකථන අංකය',
            'track_btn'           => 'සොයන්න',

            // Navigation
            'nav_shop_all'        => 'සියල්ල බලන්න',
            'nav_baddie'          => 'බැඩී / කැරලිකාර',
            'nav_oversized'       => '220 GSM ඕවර්සයිස්',
            'nav_luxury'          => 'ලක්ශරි මයින්ඩ්සෙට්',
            'nav_couple'          => 'කපල් කට්ටල',
            'nav_henley'          => 'ක්ලැසික් හෙන්ලි',

            // Hero Slider
            'hero_slide1_badge'   => 'ප්‍රිමියම් ටී-ෂර්ට් මුද්‍රණය සහ ඇඟලුම්',
            'hero_slide1_title'   => 'ඔබේම විලාසිතාවකින් බැබලෙන්න!',
            'hero_slide1_sub'     => 'අපි උසස් තත්ත්වයේ ටී-ෂර්ට් මුද්‍රණය සහ සුවිශේෂී ඇඟලුම් සඳහා විශේෂඥයෝ වෙමු. 180-220 GSM කෝම්බ්ඩ් කපු, කල්පවතින ප්‍රින්ට් සහ නවීන මෝස්තර සහිතයි.',
            'hero_slide2_badge'   => 'හෙවිවේට් ස්ට්‍රීට්වෙයාර්',
            'hero_slide2_title'   => '220 GSM ඕවර්සයිස් එකතුව',
            'hero_slide2_sub'     => 'සුවපහසු ඩ්‍රොප්-ෂෝල්ඩර් හැඩය, සුපිරි බරැති කපු රෙදිපිළි සහ නොමැකෙන දීප්තිමත් ප්‍රින්ට්.',
            'hero_slide3_badge'   => 'ගැලපෙන යුගල ඇඳුම්',
            'hero_slide3_title'   => 'කපල් සහ හෙන්ලි එකතුව',
            'hero_slide3_sub'     => 'මොනොක්‍රෝම් කපල් කට්ටල සහ එදිනෙදා භාවිතයට සුදුසු මෘදු හෙන්ලි ටී-ෂර්ට් පෙළගැස්ම.',
            'explore_collection'  => 'විලාසිතා බලන්න',
            'view_oversized'      => '220 GSM ඕවර්සයිස්',
            'custom_printing'     => 'කස්ටම් මුද්‍රණ',

            // Value Props
            'delivery_title'      => 'දිවයින පුරා බෙදාහැරීම',
            'delivery_desc'       => 'කොළඹ අවට දින 1–3 යි. පිට පළාත් සඳහා දින 3–5 යි.',
            'exchange_title'      => 'දින 14 ක ප්‍රමාණ හුවමාරුව',
            'exchange_desc'       => 'ප්‍රමාණය මාරු කර ගැනීමට නිවසටම පැමිණෙන පහසු සේවාව.',
            'cod_title'           => 'COD සහ KOKO පහසුකම්',
            'cod_desc'            => 'භාණ්ඩ ලැබුණු පසු මුදල් ගෙවන්න හෝ Koko මඟින් කොටස් 3 කින් ගෙවන්න.',
            'cotton_title'        => '100% කෝම්බ්ඩ් කපු',
            'cotton_desc'         => 'හැකිලීමෙන් තොර, නොමැකෙන වර්ණ සහිත උසස්ම නිමාව.',

            // Products & Catalog
            'new_arrivals_tag'    => 'අලුත්ම විලාසිතා',
            'new_arrivals_title'  => 'සුවිශේෂී ටී-ෂර්ට් එකතුව',
            'view_all_arrow'      => 'සියලුම නිෂ්පාදන බලන්න →',
            'view_details'        => 'විස්තර බලන්න',
            'spec_material'       => 'කෝම්බ්ඩ් කපු',

            // Categories
            'category_sec_tag'    => 'මෝස්තර අනුව තෝරන්න',
            'category_sec_title'  => 'කැටගරි අනුව බලන්න',
            'explore_category'    => 'විලාසිතා බලන්න →',

            // Single Product Page
            'select_size'         => 'ප්‍රමාණය තෝරන්න:',
            'in_stock'            => 'තොග ඇත (IN STOCK)',
            'low_stock'           => 'සීමිත තොග — ඉතිරිව ඇත්තේ {count}ක් පමණි',
            'out_of_stock'        => 'තොග අවසන් (OUT OF STOCK)',
            'add_to_bag'          => 'බෑගයට දමන්න',
            'added_to_bag'        => 'බෑගයට එක් විය',
            'buy_now_guest'       => 'ගෙස්ට් චෙක්අවුට් මඟින් දැන්ම ගන්න',
            'tech_specs_title'    => 'නිෂ්පාදන තාක්ෂණික විස්තර',
            'material_label'      => 'රෙදි වර්ගය',
            'gsm_label'           => 'GSM / බර',
            'colour_label'        => 'වර්ණය',
            'fit_label'           => 'හැඩය (FIT)',
            'standards_label'     => 'ප්‍රමිතිය',
            'specialities_label'  => 'සුවිශේෂතා',
            'exchange_label'      => 'හුවමාරුව',
            'delivery_label'      => 'බෙදාහැරීම',

            // Footer
            'footer_about'        => 'Amethyst යනු උසස් තත්ත්වයේ ටී-ෂර්ට් මුද්‍රණය, හෙවිවේට් ස්ට්‍රීට්වෙයාර් සහ කස්ටම් ඇඳුම් සඳහා ශ්‍රී ලංකාවේ ප්‍රමුඛතම නාමයකි.',
            'footer_categories'   => 'කැටගරි',
            'footer_help'         => 'සේවාවන්',
            'footer_contact'      => 'සම්බන්ධ වන්න',
            'footer_follow'       => 'අප හා එක්වන්න',
            'footer_rights'       => 'සියලුම හිමිකම් ඇවිරිණි.',
        ]
    ];

    return $strings;
}

function __(string $key, array $replace = []): string
{
    $lang = current_lang();
    $dict = lang_strings();

    $text = $dict[$lang][$key] ?? ($dict['en'][$key] ?? $key);

    if (!empty($replace)) {
        foreach ($replace as $k => $v) {
            $text = str_replace('{' . $k . '}', (string)$v, $text);
        }
    }

    return $text;
}
