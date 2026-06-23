<?php

function getCart(): array
{
    return $_SESSION['cart'] ?? [];
}

function saveCart(array $cart): void
{
    $_SESSION['cart'] = $cart;
}

function cartCount(): int
{
    return array_sum(array_column(getCart(), 'quantity'));
}

function cartSubtotal(): float
{
    $total = 0;
    foreach (getCart() as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function isFreeDelivery(): bool
{
    return cartSubtotal() >= FREE_SHIPPING_THRESHOLD;
}

function shippingFee(): float
{
    return isFreeDelivery() ? 0 : SHIPPING_FEE;
}

function cartTotal(): float
{
    return cartSubtotal() + shippingFee();
}

function addToCart(array $product, ?int $size = null, int $qty = 1): void
{
    $cart = getCart();
    $key = $product['id'] . '-' . ($size ?? 'acc');

    if (isset($cart[$key])) {
        $cart[$key]['quantity'] += $qty;
    } else {
        $cart[$key] = [
            'key'         => $key,
            'product_id'  => (int) $product['id'],
            'name'        => $product['name'],
            'brand'       => $product['brand'],
            'price'       => (float) $product['price'],
            'size'        => $size,
            'quantity'    => $qty,
            'shoe_color'  => $product['shoe_color'] ?? '#FFFFFF',
            'accent_color'=> $product['accent_color'] ?? '#FF3B00',
            'image_url'   => $product['image_url'] ?? null,
            'is_accessory'=> (bool) ($product['is_accessory'] ?? false),
        ];
    }
    saveCart($cart);
}

function updateCartQty(string $key, int $qty): void
{
    $cart = getCart();
    if ($qty <= 0) {
        unset($cart[$key]);
    } elseif (isset($cart[$key])) {
        $cart[$key]['quantity'] = $qty;
    }
    saveCart($cart);
}

function removeFromCart(string $key): void
{
    $cart = getCart();
    unset($cart[$key]);
    saveCart($cart);
}

function clearCart(): void
{
    unset($_SESSION['cart']);
}

function getWishlist(): array
{
    if (currentUser()) {
        $stmt = db()->prepare('SELECT product_id FROM wishlist WHERE user_id = ?');
        $stmt->execute([currentUser()['id']]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }
    return $_SESSION['wishlist'] ?? [];
}

function toggleWishlist(int $productId): bool
{
    if (currentUser()) {
        $uid = currentUser()['id'];
        $stmt = db()->prepare('SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?');
        $stmt->execute([$uid, $productId]);
        if ($stmt->fetch()) {
            db()->prepare('DELETE FROM wishlist WHERE user_id = ? AND product_id = ?')->execute([$uid, $productId]);
            return false;
        }
        db()->prepare('INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)')->execute([$uid, $productId]);
        return true;
    }

    $list = $_SESSION['wishlist'] ?? [];
    if (in_array($productId, $list, true)) {
        $_SESSION['wishlist'] = array_values(array_diff($list, [$productId]));
        return false;
    }
    $list[] = $productId;
    $_SESSION['wishlist'] = $list;
    return true;
}

function isWishlisted(int $productId): bool
{
    return in_array($productId, getWishlist(), true);
}

function getCompare(): array
{
    return array_slice($_SESSION['compare'] ?? [], 0, 2);
}

function setCompare(array $productIds): array
{
    $compare = [];
    foreach ($productIds as $productId) {
        $productId = (int) $productId;
        if ($productId > 0 && !in_array($productId, $compare, true)) {
            $compare[] = $productId;
        }
        if (count($compare) >= 2) {
            break;
        }
    }

    $_SESSION['compare'] = $compare;
    return $compare;
}

function setComparePrimary(int $productId): array
{
    $compare = getCompare();
    $secondary = $compare[1] ?? null;
    $next = [$productId];
    if ($secondary && $secondary !== $productId) {
        $next[] = $secondary;
    }
    return setCompare($next);
}

function addCompareSecondary(int $productId): array
{
    $compare = getCompare();
    if (empty($compare)) {
        return setCompare([$productId]);
    }

    $primary = $compare[0];
    if ($primary === $productId) {
        return $compare;
    }

    return setCompare([$primary, $productId]);
}

function toggleCompare(int $productId): array
{
    $compare = getCompare();
    if (in_array($productId, $compare, true)) {
        $compare = array_values(array_diff($compare, [$productId]));
        return setCompare($compare);
    }

    if (count($compare) < 2) {
        $compare[] = $productId;
        return setCompare($compare);
    }

    return $compare;
}

/**
 * Re-sync cart line prices and metadata from the database (prevents tampered session prices).
 */
function syncCartFromDatabase(): ?string
{
    $cart = getCart();
    if (empty($cart)) {
        return null;
    }

    $synced = [];
    foreach ($cart as $key => $item) {
        $product = getProductById((int) $item['product_id']);
        if (!$product) {
            continue;
        }

        $size = $item['size'] !== null ? (int) $item['size'] : null;
        if (!$product['is_accessory'] && $size === null) {
            return 'Invalid cart item: size required for ' . $product['name'];
        }

        if (!$product['is_accessory'] && !in_array($size, $product['sizes'], true)) {
            return 'Size UK ' . $size . ' is no longer available for ' . $product['name'];
        }

        $synced[$key] = [
            'key'          => $key,
            'product_id'   => (int) $product['id'],
            'name'         => $product['name'],
            'brand'        => $product['brand'],
            'price'        => (float) $product['price'],
            'size'         => $size,
            'quantity'     => max(1, min(10, (int) $item['quantity'])),
            'shoe_color'   => $product['shoe_color'] ?? '#FFFFFF',
            'accent_color' => $product['accent_color'] ?? '#FF3B00',
            'image_url'    => $product['image_url'] ?? null,
            'is_accessory' => (bool) $product['is_accessory'],
        ];
    }

    if (empty($synced)) {
        clearCart();
        return 'Your cart contained invalid items and was cleared.';
    }

    saveCart($synced);
    return null;
}

/**
 * Verify inventory before placing an order.
 */
function validateCartInventory(): ?string
{
    foreach (getCart() as $item) {
        $productId = (int) $item['product_id'];
        $qty = (int) $item['quantity'];

        $stmt = db()->prepare('SELECT is_accessory, stock_units, name FROM products WHERE id = ? AND is_active = 1');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if (!$product) {
            return 'A product in your cart is no longer available.';
        }

        if ((int) $product['is_accessory'] === 1) {
            if ($product['stock_units'] !== null && (int) $product['stock_units'] < $qty) {
                return $product['name'] . ' has insufficient stock.';
            }
            continue;
        }

        $size = (int) $item['size'];
        $sizeStmt = db()->prepare('SELECT stock_qty FROM product_sizes WHERE product_id = ? AND size_uk = ?');
        $sizeStmt->execute([$productId, $size]);
        $sizeRow = $sizeStmt->fetch();
        if (!$sizeRow || (int) $sizeRow['stock_qty'] < $qty) {
            return $product['name'] . ' (UK ' . $size . ') is out of stock or low on stock.';
        }
    }

    return null;
}

function decrementCartStock(PDO $pdo): void
{
    foreach (getCart() as $item) {
        $productId = (int) $item['product_id'];
        $qty = (int) $item['quantity'];

        if (!empty($item['is_accessory'])) {
            $pdo->prepare(
                'UPDATE products SET stock_units = stock_units - ? WHERE id = ? AND stock_units IS NOT NULL'
            )->execute([$qty, $productId]);
        } else {
            $pdo->prepare(
                'UPDATE product_sizes SET stock_qty = stock_qty - ? WHERE product_id = ? AND size_uk = ?'
            )->execute([$qty, $productId, (int) $item['size']]);
        }
    }
}
