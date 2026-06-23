<?php

function adminPasswordHash(): string
{
    return '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
}

function adminCredentials(): array
{
    return [
        [
            'email' => 'admin@shoebox.local',
            'password_hash' => adminPasswordHash(),
            'name' => 'Shoebox Admin',
            'role' => 'admin',
            'store_id' => null,
        ],
        [
            'email' => 'kochimanager@shoebox.local',
            'password_hash' => adminPasswordHash(),
            'name' => 'Kochi Store Manager',
            'role' => 'store_manager',
            'store_id' => 'kochi',
        ],
        [
            'email' => 'kozhikodemanager@shoebox.local',
            'password_hash' => adminPasswordHash(),
            'name' => 'Kozhikode Store Manager',
            'role' => 'store_manager',
            'store_id' => 'kozhikode',
        ],
        [
            'email' => 'thrissurmanager@shoebox.local',
            'password_hash' => adminPasswordHash(),
            'name' => 'Thrissur Store Manager',
            'role' => 'store_manager',
            'store_id' => 'thrissur',
        ],
    ];
}

function getAdminUserByEmail(string $email): ?array
{
    try {
        $stmt = db()->prepare(
            'SELECT *
             FROM admin_users
             WHERE email = ?
             LIMIT 1'
        );
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if ($admin && is_array($admin)) {
            return [
                'id' => (int) ($admin['id'] ?? 0),
                'email' => (string) ($admin['email'] ?? $email),
                'password_hash' => (string) ($admin['password_hash'] ?? $admin['password'] ?? ''),
                'full_name' => (string) ($admin['full_name'] ?? $admin['name'] ?? 'Admin'),
                'role' => (string) ($admin['role'] ?? 'admin'),
                'store_id' => $admin['store_id'] ?? null,
                'is_active' => array_key_exists('is_active', $admin) ? (int) $admin['is_active'] : 1,
                'fallback' => false,
            ];
        }
    } catch (Throwable $e) {
        // Fallback to seeded demo credentials if the admin table/migration is missing.
    }

    foreach (adminCredentials() as $admin) {
        if (strcasecmp($email, $admin['email']) === 0) {
            return [
                'id' => 0,
                'email' => $admin['email'],
                'password_hash' => $admin['password_hash'],
                'full_name' => $admin['name'],
                'role' => $admin['role'],
                'store_id' => $admin['store_id'],
                'is_active' => 1,
                'fallback' => true,
            ];
        }
    }

    return null;
}

function adminPasswordMatches(string $password, array $admin): bool
{
    $stored = (string) ($admin['password_hash'] ?? $admin['password'] ?? '');
    if ($stored === '') {
        return false;
    }

    if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon2') || str_starts_with($stored, '$argon2i$') || str_starts_with($stored, '$argon2id$')) {
        return password_verify($password, $stored);
    }

    return hash_equals($stored, $password);
}

function adminLogin(string $email, string $password): bool
{
    $admin = getAdminUserByEmail($email);
    if (!$admin || !(int) $admin['is_active']) {
        return false;
    }

    if (!adminPasswordMatches($password, $admin)) {
        return false;
    }

    if (function_exists('logoutUser')) {
        logoutUser();
    }
    regenerateSession();
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_name'] = $admin['full_name'];
    $_SESSION['role'] = $admin['role'];
    $_SESSION['store_id'] = $admin['store_id'];
    $_SESSION['admin_email'] = $admin['email'];

    if (empty($admin['fallback'])) {
        db()->prepare('UPDATE admin_users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?')
            ->execute([(int) $admin['id']]);
    }

    return true;
}

function logoutAdmin(): void
{
    unset(
        $_SESSION['admin_id'],
        $_SESSION['admin_name'],
        $_SESSION['role'],
        $_SESSION['store_id'],
        $_SESSION['admin_email']
    );
}

function currentAdmin(): ?array
{
    if (empty($_SESSION['role']) || empty($_SESSION['admin_email'])) {
        return null;
    }

    return [
        'name' => $_SESSION['admin_name'] ?? 'Admin',
        'email' => $_SESSION['admin_email'],
        'role' => $_SESSION['role'],
        'store_id' => $_SESSION['store_id'] ?? null,
    ];
}

function adminIsRole(string|array $allowedRoles): bool
{
    $roles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
    $role = $_SESSION['role'] ?? '';
    return in_array($role, $roles, true);
}

function requireAdminRole(string|array $allowedRoles = ['admin', 'store_manager']): void
{
    if (!currentAdmin()) {
        redirect('login.php');
    }

    if (!adminIsRole($allowedRoles)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function adminRoleLabel(?string $role): string
{
    return match ($role) {
        'admin' => 'Admin',
        'store_manager' => 'Store Manager',
        default => 'Guest',
    };
}

function adminStoreLabel(?string $storeId): string
{
    return match ($storeId) {
        'kochi' => 'Kochi',
        'kozhikode' => 'Kozhikode',
        'thrissur' => 'Thrissur',
        default => 'All Stores',
    };
}

function adminStatusBadgeClass(string $status): string
{
    return match ($status) {
        'pending', 'placed', 'draft' => 'badge badge-gray',
        'processing', 'packed' => 'badge badge-amber',
        'shipped', 'transit' => 'badge badge-blue',
        'delivered' => 'badge badge-green',
        'cancelled', 'rejected', 'failed' => 'badge badge-red',
        'approved', 'active', 'ready', 'featured' => 'badge badge-green',
        default => 'badge badge-neutral',
    };
}

function adminActiveClass(string $page, string $currentPage): string
{
    return trim($page === $currentPage ? 'active' : '');
}

function adminRoute(string $page): string
{
    return url('admin/' . ltrim($page, '/'));
}

function adminUploadRules(array $file, array $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'], int $maxKb = 4096): ?string
{
    if (empty($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'The upload failed. Please try again.';
    }

    $type = $file['type'] ?? '';
    if (!in_array($type, $allowedTypes, true)) {
        return 'Only JPG, PNG, or WEBP files are allowed.';
    }

    if (($file['size'] ?? 0) > ($maxKb * 1024)) {
        return 'File must be smaller than ' . $maxKb . ' KB.';
    }

    return null;
}

function adminStoreFilter(array $rows): array
{
    $role = $_SESSION['role'] ?? '';
    $storeId = $_SESSION['store_id'] ?? null;

    if ($role !== 'store_manager' || !$storeId) {
        return $rows;
    }

    return array_values(array_filter($rows, static function (array $row) use ($storeId): bool {
        return !isset($row['store_id']) || $row['store_id'] === $storeId;
    }));
}

function adminData(): array
{
    $productImage = url('assets/images/products/airforce.png');
    $brandLogo = url('assets/images/brands/nike.png');

    return [
        'dashboardMetrics' => [
            ['label' => 'Today\'s Revenue', 'value' => formatPrice(482900), 'delta' => '+12.4%', 'tone' => 'positive'],
            ['label' => 'Orders Today', 'value' => '38', 'delta' => '+6', 'tone' => 'neutral'],
            ['label' => 'Low-Stock Alerts', 'value' => '7', 'delta' => 'Needs attention', 'tone' => 'warning'],
            ['label' => 'Active Stores', 'value' => '3', 'delta' => 'Kerala network', 'tone' => 'neutral'],
        ],
        'dashboardRevenueSeries' => [
            ['label' => 'Jun 01', 'value' => 132000],
            ['label' => 'Jun 05', 'value' => 144500],
            ['label' => 'Jun 10', 'value' => 210000],
            ['label' => 'Jun 15', 'value' => 182300],
            ['label' => 'Jun 20', 'value' => 259000],
            ['label' => 'Jun 22', 'value' => 482900],
        ],
        'dashboardRecentOrders' => [
            ['id' => 'SHBX-KL-123456', 'customer' => 'Shadha Mol', 'store_id' => 'kochi', 'status' => 'processing', 'amount' => 23294, 'channel' => 'delivery', 'created_at' => '2026-06-22 09:10'],
            ['id' => 'SHBX-KL-123457', 'customer' => 'Nandana P.', 'store_id' => 'kozhikode', 'status' => 'pending', 'amount' => 7499, 'channel' => 'pickup', 'created_at' => '2026-06-22 08:42'],
            ['id' => 'SHBX-KL-123458', 'customer' => 'Akhil R.', 'store_id' => 'thrissur', 'status' => 'shipped', 'amount' => 16999, 'channel' => 'delivery', 'created_at' => '2026-06-22 08:18'],
            ['id' => 'SHBX-KL-123459', 'customer' => 'Aswathy Menon', 'store_id' => 'kozhikode', 'status' => 'delivered', 'amount' => 7849, 'channel' => 'pickup', 'created_at' => '2026-06-21 19:55'],
        ],
        'dashboardTopProducts' => [
            ['name' => 'Air Force 1 07 Triple White', 'store_id' => 'kochi', 'sold' => 84, 'stock' => 12],
            ['name' => 'Samba OG Gum Sole', 'store_id' => 'kozhikode', 'sold' => 73, 'stock' => 8],
            ['name' => 'GEL-KAYANO 14 Metropolis', 'store_id' => 'thrissur', 'sold' => 61, 'stock' => 5],
        ],
        'dashboardStoreBreakdown' => [
            ['store_id' => 'kochi', 'revenue' => 176500, 'orders' => 18, 'stock_alerts' => 2],
            ['store_id' => 'kozhikode', 'revenue' => 143900, 'orders' => 11, 'stock_alerts' => 3],
            ['store_id' => 'thrissur', 'revenue' => 162500, 'orders' => 9, 'stock_alerts' => 2],
        ],
        'lowStockAlerts' => [
            ['name' => 'Nike Air Force 1', 'sku' => 'NK-AF1-TW', 'stock' => 4, 'store_id' => 'kochi'],
            ['name' => 'Puma Palermo', 'sku' => 'PU-PAL-COB', 'stock' => 3, 'store_id' => 'kozhikode'],
            ['name' => 'Shoebox Labs Volt Elite', 'sku' => 'VOLT-ELITE-V1', 'stock' => 5, 'store_id' => 'thrissur'],
        ],
        'products' => [
            ['id' => 1, 'name' => 'Air Force 1 07 Triple White', 'brand' => 'Nike', 'price' => 9695, 'stock' => 12, 'status' => 'active', 'category' => 'Classic', 'gender' => 'Unisex', 'store_id' => 'kochi', 'image' => $productImage],
            ['id' => 2, 'name' => 'Samba OG Gum Sole', 'brand' => 'Adidas Originals', 'price' => 10999, 'stock' => 8, 'status' => 'active', 'category' => 'Trending', 'gender' => 'Unisex', 'store_id' => 'kozhikode', 'image' => url('assets/images/products/samba.png')],
            ['id' => 3, 'name' => 'GEL-KAYANO 14 Metropolis', 'brand' => 'Asics', 'price' => 13999, 'stock' => 5, 'status' => 'low_stock', 'category' => 'New Arrival', 'gender' => 'Men', 'store_id' => 'thrissur', 'image' => url('assets/images/products/gelkayano.png')],
            ['id' => 4, 'name' => 'Palermo Cobalt Blue', 'brand' => 'Puma', 'price' => 7499, 'stock' => 3, 'status' => 'low_stock', 'category' => 'Sale', 'gender' => 'Women', 'store_id' => 'kochi', 'image' => url('assets/images/products/palmero.png')],
            ['id' => 5, 'name' => 'Volt Elite V1 Obsidian Crimson', 'brand' => 'Shoebox Labs', 'price' => 18999, 'stock' => 7, 'status' => 'active', 'category' => 'Exclusive Drop', 'gender' => 'Unisex', 'store_id' => 'thrissur', 'image' => url('assets/images/products/airjordan.png')],
        ],
        'brands' => [
            ['name' => 'Nike', 'logo' => url('assets/images/brands/nike.png'), 'description' => 'Performance and street-ready essentials.', 'featured' => true],
            ['name' => 'Adidas Originals', 'logo' => url('assets/images/brands/adidas.png'), 'description' => 'Classic silhouettes with terrace energy.', 'featured' => true],
            ['name' => 'New Balance', 'logo' => url('assets/images/brands/new-balance.png'), 'description' => 'Technical runners and premium lifestyle pairs.', 'featured' => true],
            ['name' => 'Puma', 'logo' => url('assets/images/brands/puma.png'), 'description' => 'Sport heritage with bold color stories.', 'featured' => false],
            ['name' => 'Asics', 'logo' => url('assets/images/brands/asics.png'), 'description' => 'GEL cushioning and engineered comfort.', 'featured' => false],
        ],
        'collections' => [
            ['name' => 'Kerala Summer Essentials', 'image' => url('assets/images/products/chuck70.png'), 'featured' => true, 'products' => 8],
            ['name' => 'Store Pickup Capsule', 'image' => url('assets/images/products/airforce.png'), 'featured' => false, 'products' => 5],
            ['name' => 'Monsoon Ready Rotation', 'image' => url('assets/images/products/gelkayano.png'), 'featured' => true, 'products' => 6],
        ],
        'reviews' => [
            ['customer' => 'Aarav', 'product' => 'Air Force 1 07 Triple White', 'rating' => 5, 'status' => 'pending', 'comment' => 'Perfect fit and quick delivery.'],
            ['customer' => 'Meera', 'product' => 'Palermo Cobalt Blue', 'rating' => 4, 'status' => 'pending', 'comment' => 'Great color, box arrived clean.'],
            ['customer' => 'Nikhil', 'product' => 'GEL-KAYANO 14 Metropolis', 'rating' => 5, 'status' => 'approved', 'comment' => 'Best comfort in the rotation.'],
        ],
        'orders' => [
            ['id' => 'SHBX-KL-123456', 'customer' => 'Shadha Mol', 'email' => 'shadhamol2020@gmail.com', 'status' => 'processing', 'store_id' => 'kochi', 'amount' => 23294, 'channel' => 'delivery', 'created_at' => '2026-06-22 09:10'],
            ['id' => 'SHBX-KL-123457', 'customer' => 'Nandana P.', 'email' => 'nandana@example.com', 'status' => 'pending', 'store_id' => 'kozhikode', 'amount' => 7499, 'channel' => 'pickup', 'created_at' => '2026-06-22 08:42'],
            ['id' => 'SHBX-KL-123458', 'customer' => 'Akhil R.', 'email' => 'akhil@example.com', 'status' => 'shipped', 'store_id' => 'thrissur', 'amount' => 16999, 'channel' => 'delivery', 'created_at' => '2026-06-22 08:18'],
            ['id' => 'SHBX-KL-123459', 'customer' => 'Aswathy Menon', 'email' => 'aswathy@gmail.com', 'status' => 'delivered', 'store_id' => 'kozhikode', 'amount' => 7849, 'channel' => 'pickup', 'created_at' => '2026-06-21 19:55'],
        ],
        'orderDetail' => [
            'order_id' => 'SHBX-KL-123456',
            'customer' => ['name' => 'Shadha Mol', 'phone' => '+91 94475 88990', 'email' => 'shadhamol2020@gmail.com', 'address' => 'Flat No. 5C, Skyline Heritage Apartments, Kakkanad', 'store_id' => 'kochi'],
            'shipping' => ['method' => 'Courier delivery', 'carrier' => 'Kerala Express Logistics', 'pickup_store' => 'Lulu Mall, Kochi'],
            'items' => [
                ['name' => '550 White Rain Cloud', 'qty' => 1, 'size' => 8, 'price' => 14999],
                ['name' => 'Air Jordan 1 Low Shadow', 'qty' => 1, 'size' => 9, 'price' => 8295],
            ],
        ],
        'returns' => [
            ['id' => 'RET-001', 'order_id' => 'SHBX-KL-123456', 'customer' => 'Shadha Mol', 'reason' => 'Size issue', 'status' => 'pending', 'store_id' => 'kochi'],
            ['id' => 'RET-002', 'order_id' => 'SHBX-KL-123459', 'customer' => 'Aswathy Menon', 'reason' => 'Changed mind', 'status' => 'approved', 'store_id' => 'kozhikode'],
        ],
        'reports' => [
            ['label' => 'Revenue', 'value' => formatPrice(1452300)],
            ['label' => 'Orders', 'value' => '296'],
            ['label' => 'Average order', 'value' => formatPrice(4907)],
            ['label' => 'Conversion', 'value' => '3.8%'],
        ],
        'reportRows' => [
            ['date' => '2026-06-20', 'revenue' => 153400, 'orders' => 31, 'store_id' => 'kochi'],
            ['date' => '2026-06-21', 'revenue' => 174200, 'orders' => 35, 'store_id' => 'kozhikode'],
            ['date' => '2026-06-22', 'revenue' => 192900, 'orders' => 38, 'store_id' => 'thrissur'],
        ],
        'stores' => [
            ['id' => 'kochi', 'name' => 'Lulu Mall, Kochi', 'address' => 'Level 1, Lulu Mall, Edappally, Kochi', 'phone' => '+91 484 2727 888', 'whatsapp' => '+91 90796 44290', 'hours' => '10:00 AM - 10:00 PM', 'photo' => url('assets/images/products/airforce.png')],
            ['id' => 'kozhikode', 'name' => 'HiLite, Kozhikode', 'address' => 'Ground Floor, HiLite Mall, G.A. College Road, Kozhikode', 'phone' => '+91 495 2434 999', 'whatsapp' => '+91 90796 44291', 'hours' => '10:00 AM - 10:30 PM', 'photo' => url('assets/images/products/samba.png')],
            ['id' => 'thrissur', 'name' => 'Sobha City, Thrissur', 'address' => 'First Floor, Sobha City Mall, Puzhakkal, Thrissur', 'phone' => '+91 487 2323 777', 'whatsapp' => '+91 90796 44292', 'hours' => '10:00 AM - 10:00 PM', 'photo' => url('assets/images/products/gelkayano.png')],
        ],
        'inventory' => [
            ['store_id' => 'kochi', 'product' => 'Air Force 1 07 Triple White', 'sku' => 'NK-AF1-TW', 'size' => '7-11', 'stock' => 12, 'low_stock' => false],
            ['store_id' => 'kochi', 'product' => 'Palermo Cobalt Blue', 'sku' => 'PU-PAL-COB', 'size' => '7-11', 'stock' => 3, 'low_stock' => true],
            ['store_id' => 'kozhikode', 'product' => 'Samba OG Gum Sole', 'sku' => 'ADI-SAMBA-GS', 'size' => '6-10', 'stock' => 8, 'low_stock' => false],
            ['store_id' => 'thrissur', 'product' => 'Volt Elite V1 Obsidian Crimson', 'sku' => 'VOLT-ELITE-V1', 'size' => '7-11', 'stock' => 7, 'low_stock' => false],
        ],
        'pickups' => [
            ['order_id' => 'SHBX-KL-123457', 'customer' => 'Nandana P.', 'store_id' => 'kozhikode', 'status' => 'ready', 'pickup_code' => 'PK-2044'],
            ['order_id' => 'SHBX-KL-123462', 'customer' => 'Aneesh', 'store_id' => 'kochi', 'status' => 'pending', 'pickup_code' => 'PK-2091'],
            ['order_id' => 'SHBX-KL-123468', 'customer' => 'Fathima', 'store_id' => 'thrissur', 'status' => 'ready', 'pickup_code' => 'PK-2180'],
        ],
        'customers' => [
            ['id' => 1, 'name' => 'Shadha Mol', 'email' => 'shadhamol2020@gmail.com', 'phone' => '+91 94475 88990', 'orders' => 6, 'lifetime_value' => 62349, 'store_id' => 'kochi'],
            ['id' => 2, 'name' => 'Aswathy Menon', 'email' => 'aswathy@gmail.com', 'phone' => '+91 98455 77112', 'orders' => 4, 'lifetime_value' => 38820, 'store_id' => 'kozhikode'],
            ['id' => 3, 'name' => 'Akhil Raj', 'email' => 'akhil@example.com', 'phone' => '+91 99950 44112', 'orders' => 2, 'lifetime_value' => 20894, 'store_id' => 'thrissur'],
        ],
        'customerDetail' => [
            'profile' => ['name' => 'Shadha Mol', 'email' => 'shadhamol2020@gmail.com', 'phone' => '+91 94475 88990', 'city' => 'Kochi', 'points' => 320],
            'orders' => [
                ['id' => 'SHBX-KL-123456', 'status' => 'processing', 'amount' => 23294, 'created_at' => '2026-06-22'],
                ['id' => 'SHBX-KL-103455', 'status' => 'delivered', 'amount' => 14999, 'created_at' => '2026-05-18'],
            ],
        ],
        'staff' => [
            ['name' => 'Anoop K.', 'email' => 'anoop@shoebox.local', 'role' => 'admin', 'store_id' => null, 'status' => 'active'],
            ['name' => 'Megha S.', 'email' => 'megha@shoebox.local', 'role' => 'store_manager', 'store_id' => 'kochi', 'status' => 'active'],
            ['name' => 'Rohit T.', 'email' => 'rohit@shoebox.local', 'role' => 'store_manager', 'store_id' => 'kozhikode', 'status' => 'invited'],
        ],
        'promotions' => [
            ['code' => 'MONSOON10', 'type' => 'percentage', 'value' => '10%', 'expiry' => '2026-07-15', 'usage_limit' => 100, 'used' => 34, 'status' => 'active'],
            ['code' => 'FLAT500', 'type' => 'flat', 'value' => formatPrice(500), 'expiry' => '2026-08-01', 'usage_limit' => 50, 'used' => 18, 'status' => 'active'],
        ],
        'banners' => [
            ['headline' => 'Kerala Store Pickup Live', 'subhead' => 'Reserve online and collect in-store.', 'status' => 'active'],
            ['headline' => 'Monsoon Sale', 'subhead' => 'Select pairs up to 20 percent off.', 'status' => 'draft'],
        ],
        'settings' => [
            'shipping_rate' => 350,
            'tax_rate' => 18,
            'free_delivery_threshold' => 15000,
            'pickup_enabled' => true,
        ],
        'activity' => [
            ['user' => 'Shoebox Admin', 'action' => 'Updated product inventory', 'target' => 'Air Force 1 07 Triple White', 'when' => '2026-06-22 09:14'],
            ['user' => 'Megha S.', 'action' => 'Marked pickup ready', 'target' => 'SHBX-KL-123457', 'when' => '2026-06-22 08:58'],
            ['user' => 'Shoebox Admin', 'action' => 'Approved return', 'target' => 'RET-002', 'when' => '2026-06-21 21:10'],
        ],
    ];
}

function adminPageMeta(string $page): array
{
    return [
        'dashboard' => ['title' => 'Admin Dashboard', 'section' => 'dashboard', 'template' => 'dashboard', 'layout' => true],
        'products/list' => ['title' => 'Products', 'section' => 'catalog', 'template' => 'products/list', 'layout' => true],
        'products/add' => ['title' => 'Add Product', 'section' => 'catalog', 'template' => 'products/add', 'layout' => true],
        'products/edit' => ['title' => 'Edit Product', 'section' => 'catalog', 'template' => 'products/edit', 'layout' => true],
        'variants' => ['title' => 'Variants and Stock', 'section' => 'catalog', 'template' => 'variants', 'layout' => true],
        'brands' => ['title' => 'Brands', 'section' => 'catalog', 'template' => 'brands', 'layout' => true],
        'collections' => ['title' => 'Collections', 'section' => 'catalog', 'template' => 'collections', 'layout' => true],
        'reviews' => ['title' => 'Reviews', 'section' => 'sales', 'template' => 'reviews', 'layout' => true],
        'orders/list' => ['title' => 'Orders', 'section' => 'sales', 'template' => 'orders/list', 'layout' => true],
        'orders/detail' => ['title' => 'Order Detail', 'section' => 'sales', 'template' => 'orders/detail', 'layout' => true],
        'returns' => ['title' => 'Returns and Refunds', 'section' => 'sales', 'template' => 'returns', 'layout' => true],
        'reports' => ['title' => 'Reports', 'section' => 'sales', 'template' => 'reports', 'layout' => true],
        'stores/list' => ['title' => 'Store Locations', 'section' => 'stores', 'template' => 'stores/list', 'layout' => true],
        'stores/edit' => ['title' => 'Edit Store', 'section' => 'stores', 'template' => 'stores/edit', 'layout' => true],
        'stores/inventory' => ['title' => 'Store Inventory', 'section' => 'stores', 'template' => 'stores/inventory', 'layout' => true],
        'stores/pickups' => ['title' => 'Pickup Orders', 'section' => 'stores', 'template' => 'stores/pickups', 'layout' => true],
        'customers/list' => ['title' => 'Customers', 'section' => 'people', 'template' => 'customers/list', 'layout' => true],
        'customers/detail' => ['title' => 'Customer Detail', 'section' => 'people', 'template' => 'customers/detail', 'layout' => true],
        'staff' => ['title' => 'Staff and Roles', 'section' => 'people', 'template' => 'staff', 'layout' => true],
        'promotions' => ['title' => 'Promotions', 'section' => 'marketing', 'template' => 'promotions', 'layout' => true],
        'settings' => ['title' => 'Site Settings', 'section' => 'system', 'template' => 'settings', 'layout' => true],
        'activity-log' => ['title' => 'Activity Log', 'section' => 'system', 'template' => 'activity-log', 'layout' => true],
        'login' => ['title' => 'Admin Login', 'section' => '', 'template' => 'login', 'layout' => false],
        'logout' => ['title' => 'Sign Out', 'section' => '', 'template' => 'login', 'layout' => false],
    ][$page] ?? ['title' => 'Admin', 'section' => 'dashboard', 'template' => 'dashboard', 'layout' => true];
}

function adminView(string $template, array $data = []): void
{
    $templateFile = __DIR__ . '/../views/pages/admin/' . ltrim($template, '/') . '.php';
    if (!is_file($templateFile)) {
        http_response_code(404);
        echo 'Admin template not found';
        exit;
    }

    extract($data, EXTR_SKIP);
    ob_start();
    require $templateFile;
    $content = ob_get_clean();
    require __DIR__ . '/../views/layouts/admin.php';
}

function adminHandlePost(string $page, array $data): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if ($page === 'login') {
        $email = normalizeEmail(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        if (!validateEmail($email)) {
            flash('warning', 'Invalid Email', 'Enter a valid staff email address.');
            redirect('login.php');
        }

        if (!adminLogin($email, $password)) {
            flash('warning', 'Login Failed', 'Invalid admin credentials.');
            redirect('login.php');
        }

        flash('success', 'Signed In', 'Welcome back to Shoebox Admin.');
        redirect('admin/');
    }

    if ($page === 'logout') {
        logoutAdmin();
        flash('info', 'Signed Out', 'Session cleared.');
        redirect('login.php');
    }

    $allowedPages = [
        'products/add' => ['name', 'brand', 'price', 'category'],
        'products/edit' => ['name', 'brand', 'price', 'category'],
        'brands' => ['name'],
        'collections' => ['name'],
        'reviews' => ['action'],
        'orders/detail' => ['status'],
        'returns' => ['decision'],
        'stores/edit' => ['name', 'address'],
        'stores/inventory' => ['product', 'stock'],
        'stores/pickups' => ['action'],
        'customers/detail' => ['notes'],
        'staff' => ['name', 'email', 'role'],
        'promotions' => ['code'],
        'settings' => ['shipping_rate', 'tax_rate'],
    ];

    if (!isset($allowedPages[$page])) {
        return;
    }

    $errors = [];
    foreach ($allowedPages[$page] as $field) {
        if (trim((string) ($_POST[$field] ?? '')) === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
        }
    }

    if (in_array($page, ['products/add', 'products/edit', 'brands', 'stores/edit'], true) && !empty($_FILES['image']['name'] ?? '')) {
        $uploadError = adminUploadRules($_FILES['image']);
        if ($uploadError) {
            $errors[] = $uploadError;
        }
    }

    if ($errors) {
        flash('warning', 'Validation Error', implode(' ', $errors));
        redirect('admin/' . $page);
    }

    // TODO: connect to PHP backend and persist the record using PDO prepared statements.
    flash('success', 'Draft Saved', 'Validation passed. Wire this page to the database next.');
    redirect('admin/' . $page);
}

function adminCsvReportRows(array $rows): void
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="shoebox-report.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date', 'Revenue', 'Orders', 'Store']);
    foreach ($rows as $row) {
        fputcsv($out, [$row['date'], $row['revenue'], $row['orders'], adminStoreLabel($row['store_id'] ?? null)]);
    }
    fclose($out);
    exit;
}

function adminHandleLoginPost(): void
{
    requireCsrf();

    $email = normalizeEmail(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if (!validateEmail($email)) {
        flash('warning', 'Invalid Email', 'Enter a valid staff email address.');
        redirect('login.php');
    }

    $admin = getAdminUserByEmail($email);
    if (!$admin) {
        flash('warning', 'Login Failed', 'No admin account found for that email address.');
        redirect('login.php');
    }

    if (!(int) $admin['is_active']) {
        flash('warning', 'Login Failed', 'That admin account is inactive.');
        redirect('login.php');
    }

    if (!adminPasswordMatches($password, $admin)) {
        flash('warning', 'Login Failed', 'The password does not match the stored admin account.');
        redirect('login.php');
    }

    adminLogin($email, $password);
    flash('success', 'Signed In', 'Welcome back to Shoebox Admin.');
    redirect('admin/');
}







