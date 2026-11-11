<?php
/**
 * AJAX Cart Controller — Amethystclothing.lk
 * Handles Add to Bag, Quantity Update, Item Removal, and Cart State Retrieval.
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../core/helpers.php';

// Initialize session cart
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Read input (support both JSON and POST)
$rawInput = file_get_contents('php://input');
$json = json_decode($rawInput, true);
$data = is_array($json) ? $json : $_POST;

$action = $data['action'] ?? $_GET['action'] ?? 'get';
$pdo = db();

function calculate_cart_summary(array $cart): array
{
    $itemCount = 0;
    $subtotal = 0.0;
    $items = [];

    foreach ($cart as $item) {
        $qty = (int)$item['quantity'];
        $price = (float)$item['price'];
        $lineTotal = $price * $qty;
        $itemCount += $qty;
        $subtotal += $lineTotal;

        $item['line_total'] = $lineTotal;
        $item['formatted_price'] = format_price($price);
        $item['formatted_line_total'] = format_price($lineTotal);
        $items[] = $item;
    }

    return [
        'itemCount'         => $itemCount,
        'subtotal'          => $subtotal,
        'formattedSubtotal' => format_price($subtotal),
        'items'             => $items,
    ];
}

switch ($action) {
    case 'add':
        $variantId = (int)($data['variant_id'] ?? 0);
        $qty = max(1, (int)($data['quantity'] ?? 1));

        if ($variantId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product variant.']);
            exit;
        }

        // Query variant and product info
        $stmt = $pdo->prepare("
            SELECT 
                pv.id AS variant_id,
                pv.product_id,
                pv.sku,
                pv.color_name,
                pv.color_hex,
                pv.size_code,
                pv.total_stock_quantity,
                COALESCE(pv.price_override, p.sale_price, p.base_price) AS final_price,
                p.name AS product_name,
                p.slug AS product_slug,
                (SELECT url FROM product_media WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS primary_image
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.id
            WHERE pv.id = ? AND pv.is_active = 1 AND p.status = 'active'
        ");
        $stmt->execute([$variantId]);
        $variant = $stmt->fetch();

        if (!$variant) {
            echo json_encode(['success' => false, 'message' => 'Product variant not found or inactive.']);
            exit;
        }

        $stock = (int)$variant['total_stock_quantity'];
        if ($stock <= 0) {
            echo json_encode(['success' => false, 'message' => 'Selected size is out of stock.']);
            exit;
        }

        $currentQtyInCart = isset($_SESSION['cart'][$variantId]) ? (int)$_SESSION['cart'][$variantId]['quantity'] : 0;
        $newQty = $currentQtyInCart + $qty;

        if ($newQty > $stock) {
            $allowed = max(0, $stock - $currentQtyInCart);
            if ($allowed <= 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => "You already have all available items ($stock) in your bag."
                ]);
                exit;
            }
            $newQty = $stock;
        }

        $image = !empty($variant['primary_image']) 
            ? $variant['primary_image'] 
            : 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=400&h=500&q=80';

        $_SESSION['cart'][$variantId] = [
            'variant_id'   => $variantId,
            'product_id'   => (int)$variant['product_id'],
            'name'         => $variant['product_name'],
            'slug'         => $variant['product_slug'],
            'sku'          => $variant['sku'],
            'size'         => $variant['size_code'],
            'color'        => $variant['color_name'],
            'color_hex'    => $variant['color_hex'],
            'price'        => (float)$variant['final_price'],
            'stock'        => $stock,
            'image'        => $image,
            'quantity'     => $newQty,
        ];

        $summary = calculate_cart_summary($_SESSION['cart']);
        echo json_encode(array_merge(['success' => true, 'message' => 'Added to bag.'], $summary));
        exit;

    case 'update':
        $variantId = (int)($data['variant_id'] ?? 0);
        $newQty = (int)($data['quantity'] ?? 0);

        if (!isset($_SESSION['cart'][$variantId])) {
            echo json_encode(['success' => false, 'message' => 'Item not in bag.']);
            exit;
        }

        if ($newQty <= 0) {
            unset($_SESSION['cart'][$variantId]);
        } else {
            // Verify stock limit
            $stock = (int)$_SESSION['cart'][$variantId]['stock'];
            if ($newQty > $stock) {
                $newQty = $stock;
            }
            $_SESSION['cart'][$variantId]['quantity'] = $newQty;
        }

        $summary = calculate_cart_summary($_SESSION['cart']);
        echo json_encode(array_merge(['success' => true], $summary));
        exit;

    case 'remove':
        $variantId = (int)($data['variant_id'] ?? 0);
        if (isset($_SESSION['cart'][$variantId])) {
            unset($_SESSION['cart'][$variantId]);
        }
        $summary = calculate_cart_summary($_SESSION['cart']);
        echo json_encode(array_merge(['success' => true], $summary));
        exit;

    case 'clear':
        $_SESSION['cart'] = [];
        $summary = calculate_cart_summary($_SESSION['cart']);
        echo json_encode(array_merge(['success' => true], $summary));
        exit;

    case 'get':
    default:
        $summary = calculate_cart_summary($_SESSION['cart']);
        echo json_encode(array_merge(['success' => true], $summary));
        exit;
}
