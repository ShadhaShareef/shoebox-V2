<?php
$pageTitle = $pageTitle ?? 'Shoebox Admin';
$currentPage = $currentPage ?? 'dashboard';
$activeSection = $activeSection ?? 'dashboard';
$admin = currentAdmin();
$alerts = [];
if (isset($lowStockAlerts) && is_array($lowStockAlerts)) {
    $alerts = adminStoreFilter($lowStockAlerts);
}
if (isset($orders) && is_array($orders)) {
    $alerts = array_merge($alerts, adminStoreFilter($orders));
}
$alertCount = count($alerts);

$navGroups = [
    'Catalog' => [
        ['label' => 'Products', 'page' => 'products/list'],
        ['label' => 'Add Product', 'page' => 'products/add'],
        ['label' => 'Variants', 'page' => 'variants'],
        ['label' => 'Brands', 'page' => 'brands'],
        ['label' => 'Collections', 'page' => 'collections'],
        ['label' => 'Reviews', 'page' => 'reviews'],
    ],
    'Sales' => [
        ['label' => 'Orders', 'page' => 'orders/list'],
        ['label' => 'Returns', 'page' => 'returns'],
        ['label' => 'Reports', 'page' => 'reports'],
    ],
    'Stores' => [
        ['label' => 'Store Locations', 'page' => 'stores/list'],
        ['label' => 'Inventory', 'page' => 'stores/inventory'],
        ['label' => 'Pickup Orders', 'page' => 'stores/pickups'],
    ],
    'People' => [
        ['label' => 'Customers', 'page' => 'customers/list'],
        ['label' => 'Staff', 'page' => 'staff'],
    ],
    'Marketing' => [
        ['label' => 'Promotions', 'page' => 'promotions'],
    ],
    'System' => [
        ['label' => 'Settings', 'page' => 'settings'],
        ['label' => 'Activity Log', 'page' => 'activity-log'],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
  <title><?= e($pageTitle) ?> | Shoebox Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>?v=<?= time() ?>">
</head>
<body class="admin-body">
<div class="admin-shell" data-admin-shell>
  <aside class="admin-sidebar" id="admin-sidebar">
    <div class="admin-brand">
      <div>
        <p class="admin-brand-kicker">Shoebox</p>
        <a href="<?= adminRoute('dashboard') ?>" class="admin-brand-name">Admin Panel</a>
      </div>
      <button type="button" class="icon-button mobile-only" data-sidebar-close aria-label="Close sidebar">×</button>
    </div>

    <div class="admin-role-card">
      <span class="badge <?= ($admin && $admin['role'] === 'admin') ? 'badge-green' : 'badge-amber' ?>"><?= e(adminRoleLabel($admin['role'] ?? null)) ?></span>
      <strong><?= e($admin['name'] ?? 'Guest') ?></strong>
      <small><?= e($admin['email'] ?? 'Not signed in') ?></small>
      <small><?= e(adminStoreLabel($admin['store_id'] ?? null)) ?></small>
    </div>

    <?php foreach ($navGroups as $group => $items): ?>
      <div class="nav-group">
        <p class="nav-group-title"><?= e($group) ?></p>
        <nav class="nav-links">
          <?php foreach ($items as $item): ?>
            <?php
              $itemActive = $currentPage === $item['page'] || str_starts_with($currentPage, $item['page'] . '/');
            ?>
            <a class="nav-link <?= $itemActive ? 'active' : '' ?>" href="<?= adminRoute($item['page']) ?>">
              <span><?= e($item['label']) ?></span>
              <span class="nav-pill"></span>
            </a>
          <?php endforeach; ?>
        </nav>
      </div>
    <?php endforeach; ?>
  </aside>

  <div class="admin-overlay" id="admin-overlay" data-sidebar-close></div>

  <div class="admin-panel">
    <header class="admin-topbar">
      <div class="topbar-left">
        <button type="button" class="icon-button" data-sidebar-open aria-label="Open sidebar">☰</button>
        <div>
          <p class="topbar-eyebrow">Internal staff dashboard</p>
          <h1><?= e($pageTitle) ?></h1>
        </div>
      </div>
      <div class="topbar-center">
        <label class="search-box">
          <span>Search</span>
          <input type="search" placeholder="Search products, orders, customers" data-admin-search>
        </label>
      </div>
      <div class="topbar-right">
        <button type="button" class="alert-button" data-alert-toggle>
          Alerts
          <?php if ($alertCount > 0): ?><span class="alert-count"><?= (int) $alertCount ?></span><?php endif; ?>
        </button>
        <div class="admin-alerts" data-alert-panel hidden>
          <div class="admin-alerts-head">
            <strong>Alerts</strong>
            <span><?= (int) $alertCount ?> open items</span>
          </div>
          <div class="admin-alerts-list">
            <?php foreach (array_slice($alerts, 0, 6) as $alert): ?>
              <div class="admin-alert-item">
                <strong><?= e($alert['name'] ?? ($alert['customer'] ?? 'Alert')) ?></strong>
                <span><?= e($alert['sku'] ?? ($alert['status'] ?? 'pending')) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="profile-menu">
          <button type="button" class="profile-trigger" data-profile-toggle>
            <span class="profile-avatar"><?= e(strtoupper(substr($admin['name'] ?? 'A', 0, 1))) ?></span>
            <span class="profile-meta">
              <strong><?= e($admin['name'] ?? 'Admin') ?></strong>
              <small><?= e(adminRoleLabel($admin['role'] ?? null)) ?></small>
            </span>
          </button>
          <div class="profile-dropdown" data-profile-panel hidden>
            <a href="<?= adminRoute('settings') ?>">Settings</a>
            <form action="<?= adminRoute('logout') ?>" method="POST" class="profile-logout">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="logout">
              <button type="submit">Sign out</button>
            </form>
          </div>
        </div>
      </div>
    </header>

    <main class="admin-content">
      <?php foreach (getFlashes() as $flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>">
          <strong><?= e($flash['title']) ?></strong>
          <?php if (!empty($flash['description'])): ?><p><?= e($flash['description']) ?></p><?php endif; ?>
        </div>
      <?php endforeach; ?>

      <?= $content ?>
    </main>
  </div>
</div>

<script src="<?= url('public/js/admin.js') ?>?v=<?= time() ?>"></script>
<script>
  window.SHOEBOX_ADMIN = {
    csrf: '<?= e(csrfToken()) ?>',
    currentPage: '<?= e($currentPage) ?>'
  };
</script>
</body>
</html>
