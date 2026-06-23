<?php
$metrics = $dashboardMetrics ?? [];
$revenueSeries = adminStoreFilter($dashboardRevenueSeries ?? []);
$recentOrders = adminStoreFilter($dashboardRecentOrders ?? []);
$topProducts = adminStoreFilter($dashboardTopProducts ?? []);
$storeBreakdown = adminStoreFilter($dashboardStoreBreakdown ?? []);
$lowStockAlerts = adminStoreFilter($lowStockAlerts ?? []);
$maxRevenue = max(array_column($revenueSeries, 'value')) ?: 1;
?>

<div class="page-head">
  <div>
    <p class="eyebrow">Overview</p>
    <h2>Dashboard</h2>
    <p>Current revenue, order flow, stock pressure, and store performance.</p>
  </div>
  <div class="toolbar-actions">
    <a class="btn btn-secondary btn-sm" href="<?= adminRoute('reports') ?>">Open reports</a>
    <a class="btn btn-primary btn-sm" href="<?= adminRoute('products/add') ?>">Add product</a>
  </div>
</div>

<section class="grid-4">
  <?php foreach ($metrics as $metric): ?>
    <article class="metric-card">
      <p><?= e($metric['label']) ?></p>
      <strong><?= e($metric['value']) ?></strong>
      <span class="tone-<?= e($metric['tone']) ?>"><?= e($metric['delta']) ?></span>
    </article>
  <?php endforeach; ?>
</section>

<section class="grid-2" style="margin-top: 14px;">
  <article class="chart-card">
    <div class="section-head">
      <div>
        <h2>Revenue chart</h2>
        <p>Last 30 days of sales, rendered with plain HTML and CSS.</p>
      </div>
    </div>
    <div class="chart-bars">
      <?php foreach ($revenueSeries as $point): ?>
        <div class="chart-bar-wrap">
          <div class="chart-bar" style="--bar-height: <?= round(($point['value'] / $maxRevenue) * 100) ?>%;"></div>
          <div class="chart-bar-value"><?= formatPrice((float) $point['value']) ?></div>
          <div class="chart-bar-label"><?= e($point['label']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </article>

  <article class="panel-card">
    <div class="section-head">
      <div>
        <h2>Low stock alerts</h2>
        <p>Products needing attention before the next sales wave.</p>
      </div>
    </div>
    <div class="summary-list">
      <?php foreach ($lowStockAlerts as $alert): ?>
        <div class="summary-row">
          <div>
            <strong><?= e($alert['name']) ?></strong>
            <span><?= e($alert['sku']) ?> · <?= e(adminStoreLabel($alert['store_id'])) ?></span>
          </div>
          <span class="badge badge-red"><?= (int) $alert['stock'] ?> left</span>
        </div>
      <?php endforeach; ?>
    </div>
  </article>
</section>

<section class="table-card" style="margin-top: 14px;">
  <div class="table-toolbar">
    <div>
      <strong>Recent orders</strong>
      <p class="table-subtitle">Latest transactions across all Kerala stores.</p>
    </div>
    <div class="actions">
      <a class="btn btn-secondary btn-sm" href="<?= adminRoute('orders/list') ?>">View all orders</a>
    </div>
  </div>
  <div class="table-shell">
    <table class="admin-table" data-admin-table>
      <thead>
        <tr>
          <th data-sort>Order</th>
          <th data-sort>Customer</th>
          <th data-sort>Store</th>
          <th data-sort>Status</th>
          <th data-sort>Amount</th>
          <th data-sort>Time</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentOrders as $order): ?>
          <tr>
            <td>
              <div class="table-title"><?= e($order['id']) ?></div>
              <div class="table-subtitle"><?= e($order['channel'] ?? '—') ?></div>
            </td>
            <td><?= e($order['customer']) ?><div class="table-subtitle"><?= e($order['email'] ?? '') ?></div></td>
            <td><?= e(adminStoreLabel($order['store_id'])) ?></td>
            <td><span class="<?= adminStatusBadgeClass($order['status']) ?>"><?= e($order['status']) ?></span></td>
            <td><?= formatPrice((float) $order['amount']) ?></td>
            <td><?= e($order['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="grid-2" style="margin-top: 14px;">
  <article class="table-card">
    <div class="table-toolbar">
      <div>
        <strong>Top-selling products</strong>
        <p class="table-subtitle">Units sold by product, with store context.</p>
      </div>
    </div>
    <div class="table-shell">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Store</th>
            <th>Sold</th>
            <th>Stock</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($topProducts as $product): ?>
            <tr>
              <td><?= e($product['name']) ?></td>
              <td><?= e(adminStoreLabel($product['store_id'])) ?></td>
              <td><?= (int) $product['sold'] ?></td>
              <td><?= (int) $product['stock'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>

  <article class="table-card">
    <div class="table-toolbar">
      <div>
        <strong>Per-store breakdown</strong>
        <p class="table-subtitle">Revenue and order mix for the three Kerala locations.</p>
      </div>
    </div>
    <div class="table-shell">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Store</th>
            <th>Revenue</th>
            <th>Orders</th>
            <th>Alerts</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($storeBreakdown as $row): ?>
            <tr>
              <td><?= e(adminStoreLabel($row['store_id'])) ?></td>
              <td><?= formatPrice((float) $row['revenue']) ?></td>
              <td><?= (int) $row['orders'] ?></td>
              <td><span class="badge badge-amber"><?= (int) $row['stock_alerts'] ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>
</section>
