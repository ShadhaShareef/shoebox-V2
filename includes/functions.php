<?php

function url(string $path = ''): string
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function formatPrice(float $amount): string
{
    return CURRENCY . number_format($amount, 0, '.', ',');
}

function flash(string $type, string $title, string $description = ''): void
{
    $_SESSION['flash'][] = compact('type', 'title', 'description');
}

function getFlashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    $_SESSION['flash'] = [];
    return $flashes;
}

function redirect(string $path): void
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        header('Location: ' . $path);
    } else {
        header('Location: ' . url($path));
    }
    exit;
}

function jsonResponse(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function generateOrderId(): string
{
    return 'SHBX-KL-' . random_int(100000, 999999);
}

function getProducts(?string $brand = null, ?string $category = null, ?string $search = null, ?string $sort = 'popular', bool $saleOnly = false): array
{
    $sql = 'SELECT p.*, GROUP_CONCAT(ps.size_uk ORDER BY ps.size_uk) AS sizes
            FROM products p
            LEFT JOIN product_sizes ps ON ps.product_id = p.id
            WHERE p.is_active = 1 AND p.is_accessory = 0';
    $params = [];

    if ($saleOnly) {
        $sql .= ' AND p.original_price IS NOT NULL';
    }

    if ($brand && $brand !== 'All') {
        $sql .= ' AND p.brand = ?';
        $params[] = $brand;
    }

    if ($category && $category !== 'All') {
        $sql .= ' AND p.category = ?';
        $params[] = $category;
    }

    if ($search) {
        $sql .= ' AND (p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)';
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= ' GROUP BY p.id';

    switch ($sort) {
        case 'lowToHigh':
            $sql .= ' ORDER BY p.price ASC';
            break;
        case 'highToLow':
            $sql .= ' ORDER BY p.price DESC';
            break;
        default:
            $sql .= ' ORDER BY p.rating DESC, p.id ASC';
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['sizes'] = $row['sizes'] ? array_map('intval', explode(',', $row['sizes'])) : [];
        $row['price'] = (float) $row['price'];
        $row['original_price'] = $row['original_price'] !== null ? (float) $row['original_price'] : null;
        $row['rating'] = (float) $row['rating'];
    }

    return $rows;
}

function getProductById(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT p.*, GROUP_CONCAT(ps.size_uk ORDER BY ps.size_uk) AS sizes
         FROM products p
         LEFT JOIN product_sizes ps ON ps.product_id = p.id
         WHERE p.id = ? AND p.is_active = 1
         GROUP BY p.id'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }
    $row['sizes'] = $row['sizes'] ? array_map('intval', explode(',', $row['sizes'])) : [];
    $row['price'] = (float) $row['price'];
    $row['original_price'] = $row['original_price'] !== null ? (float) $row['original_price'] : null;
    return $row;
}

function getProductSpec(int $productId): ?array
{
    $stmt = db()->prepare('SELECT * FROM product_specs WHERE product_id = ?');
    $stmt->execute([$productId]);
    return $stmt->fetch() ?: null;
}

function getCompareProductById(int $productId): ?array
{
    $product = getProductById($productId);
    if (!$product) {
        return null;
    }

    $product['spec'] = getProductSpec($productId);
    return comparePayload($product);
}

function getBrands(): array
{
    $stmt = db()->query(
        "SELECT DISTINCT brand FROM products WHERE is_active = 1 AND is_accessory = 0 ORDER BY brand"
    );
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getCategories(): array
{
    $stmt = db()->query(
        "SELECT DISTINCT category FROM products WHERE is_active = 1 AND is_accessory = 0 ORDER BY category"
    );
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getAccessories(): array
{
    $stmt = db()->query('SELECT * FROM products WHERE is_accessory = 1 AND is_active = 1 ORDER BY id');
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['price'] = (float) $row['price'];
    }
    return $rows;
}

function getStores(): array
{
    $stmt = db()->query('SELECT * FROM stores ORDER BY sort_order');
    $stores = $stmt->fetchAll();

    $featStmt = db()->query('SELECT store_id, feature FROM store_features ORDER BY sort_order');
    $features = [];
    foreach ($featStmt->fetchAll() as $f) {
        $features[$f['store_id']][] = $f['feature'];
    }

    foreach ($stores as &$store) {
        $store['features'] = $features[$store['id']] ?? [];
    }
    return $stores;
}

function getFaqItems(?string $category = null, ?string $search = null): array
{
    $sql = 'SELECT * FROM faq_items WHERE 1=1';
    $params = [];

    if ($category && $category !== 'All') {
        $sql .= ' AND category = ?';
        $params[] = $category;
    }

    if ($search) {
        $sql .= ' AND (question LIKE ? OR answer LIKE ?)';
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= ' ORDER BY sort_order';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getFaqCategories(): array
{
    $stmt = db()->query('SELECT DISTINCT category FROM faq_items ORDER BY category');
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getOrderById(string $orderId): ?array
{
    $stmt = db()->prepare('SELECT * FROM orders WHERE id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order) {
        return null;
    }

    $items = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $items->execute([$orderId]);
    $order['items'] = $items->fetchAll();

    $timeline = db()->prepare('SELECT * FROM order_timeline WHERE order_id = ? ORDER BY sort_order');
    $timeline->execute([$orderId]);
    $order['timeline'] = $timeline->fetchAll();

    return $order;
}

function getUserOrders(int $userId): array
{
    $stmt = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();

    foreach ($orders as &$order) {
        $items = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $items->execute([$order['id']]);
        $order['items'] = $items->fetchAll();
    }
    return $orders;
}

function resolveMemberTier(int $points): array
{
    $stmt = db()->prepare('SELECT * FROM loyalty_tiers WHERE min_points <= ? ORDER BY min_points DESC LIMIT 1');
    $stmt->execute([$points]);
    return $stmt->fetch() ?: ['id' => 1, 'name' => 'ROOKIE SNEAKERHEAD'];
}

function getLoyaltyTiers(): array
{
    $tiers = db()->query('SELECT * FROM loyalty_tiers ORDER BY sort_order')->fetchAll();
    $benefits = db()->query('SELECT tier_id, benefit FROM loyalty_tier_benefits ORDER BY sort_order')->fetchAll();
    $map = [];
    foreach ($benefits as $b) {
        $map[$b['tier_id']][] = $b['benefit'];
    }
    foreach ($tiers as &$tier) {
        $tier['benefits'] = $map[$tier['id']] ?? [];
    }
    return $tiers;
}

function isActivePage(string $page): string
{
    $current = basename($_SERVER['PHP_SELF'], '.php');
    return $current === $page ? 'active' : '';
}

function discountPercent(float $price, float $original): int
{
    if ($original <= 0) {
        return 0;
    }
    return (int) round((($original - $price) / $original) * 100);
}

function comparePayload(array $product): array
{
    return [
        'id' => (int) $product['id'],
        'name' => $product['name'],
        'brand' => $product['brand'],
        'category' => $product['category'],
        'description' => $product['description'] ?? '',
        'price' => (float) $product['price'],
        'priceLabel' => formatPrice((float) $product['price']),
        'originalPrice' => isset($product['original_price']) && $product['original_price'] !== null ? (float) $product['original_price'] : null,
        'originalPriceLabel' => isset($product['original_price']) && $product['original_price'] !== null ? formatPrice((float) $product['original_price']) : null,
        'rating' => (float) ($product['rating'] ?? 0),
        'ratingLabel' => number_format((float) ($product['rating'] ?? 0), 1),
        'imageUrl' => $product['image_url'] ?? '',
        'shoeColor' => $product['shoe_color'] ?? '#FFFFFF',
        'accentColor' => $product['accent_color'] ?? '#FF3B00',
        'sizes' => $product['sizes'] ?? [],
        'stockUnits' => isset($product['stock_units']) ? (int) $product['stock_units'] : null,
        'stockLabel' => isset($product['stock_units']) && $product['stock_units'] !== null ? ((int) $product['stock_units'] . ' units left') : null,
        'isExclusive' => !empty($product['is_exclusive']),
        'spec' => $product['spec'] ?? null,
    ];
}
