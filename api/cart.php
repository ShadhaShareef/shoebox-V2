<?php

require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = (int) ($_POST['product_id'] ?? 0);
        $size = isset($_POST['size']) && $_POST['size'] !== '' ? (int) $_POST['size'] : null;
        $product = getProductById($productId);

        if (!$product) {
            flash('error', 'Product not found');
            redirect($_SERVER['HTTP_REFERER'] ?? 'shop.php');
        }

        if (!$product['is_accessory'] && $size === null) {
            flash('warning', 'Select a size', 'Please choose your UK size.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'shop.php');
        }

        $key = $productId . '-' . ($size ?? 'acc');
        $existingQty = getCart()[$key]['quantity'] ?? 0;
        $newQty = $existingQty + 1;

        if (!$product['is_accessory']) {
            $sizeStmt = db()->prepare('SELECT stock_qty FROM product_sizes WHERE product_id = ? AND size_uk = ?');
            $sizeStmt->execute([$productId, $size]);
            $stock = (int) ($sizeStmt->fetchColumn() ?: 0);

            if ($stock < $newQty) {
                flash('warning', 'Out of Stock', 'This size is no longer available.');
                redirect($_SERVER['HTTP_REFERER'] ?? 'shop.php');
            }
        } elseif ($product['stock_units'] !== null && (int) $product['stock_units'] < $newQty) {
            flash('warning', 'Out of Stock', 'This item is no longer available.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'shop.php');
        }

        addToCart($product, $size);
        unset($_SESSION['checkout_step'], $_SESSION['delivery'], $_SESSION['pending_razorpay_order'], $_SESSION['placed_order_id']);
        flash('success', 'Item Added', $product['name'] . ' added to your bag.');
        redirect($_SERVER['HTTP_REFERER'] ?? 'shop.php');

    case 'update':
        $key = $_POST['key'] ?? '';
        $qty = max(0, min(10, (int) ($_POST['quantity'] ?? 0)));
        $cart = getCart();

        if ($qty > 0 && isset($cart[$key])) {
            $item = $cart[$key];
            $productId = (int) $item['product_id'];

            if ($item['size']) {
                $sizeStmt = db()->prepare('SELECT stock_qty FROM product_sizes WHERE product_id = ? AND size_uk = ?');
                $sizeStmt->execute([$productId, (int) $item['size']]);
                $stock = (int) ($sizeStmt->fetchColumn() ?: 0);

                if ($stock < $qty) {
                    flash('warning', 'Stock Limit', 'Only ' . $stock . ' units available.');
                    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
                }
            }
        }

        updateCartQty($key, $qty);
        unset($_SESSION['checkout_step'], $_SESSION['delivery'], $_SESSION['pending_razorpay_order'], $_SESSION['placed_order_id']);
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');

    case 'remove':
        removeFromCart($_POST['key'] ?? '');
        unset($_SESSION['checkout_step'], $_SESSION['delivery'], $_SESSION['pending_razorpay_order'], $_SESSION['placed_order_id']);
        redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');

    default:
        redirect('index.php');
}
