<?php
$rows = adminStoreFilter(adminData()['reportRows']);
$summary = adminData()['reports'];
?>
<div class="page-head">
  <div>
    <p class="eyebrow">Sales</p>
    <h2>Reports</h2>
    <p>Revenue ranges, top products, store sales, and CSV export.</p>
  </div>
  <div class="toolbar-actions">
    <a class="btn btn-secondary btn-sm" href="<?= adminRoute('reports') ?>?export=csv">Export CSV</a>
  </div>
</div>

<section class="grid-4">
  <?php foreach ($summary as $row): ?>
    <article class="metric-card">
      <p><?= e($row['label']) ?></p>
      <strong><?= e($row['value']) ?></strong>
      <span class="tone-neutral">Summary metric</span>
    </article>
  <?php endforeach; ?>
</section>

<section class="grid-2" style="margin-top: 14px;">
  <article class="table-card">
    <div class="table-toolbar">
      <div>
        <strong>Sales by store</strong>
        <p class="table-subtitle">Revenue and order count in a simple report table.</p>
      </div>
    </div>
    <div class="table-shell">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Store</th>
            <th>Revenue</th>
            <th>Orders</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?= e($row['date']) ?></td>
              <td><?= e(adminStoreLabel($row['store_id'])) ?></td>
              <td><?= formatPrice((float) $row['revenue']) ?></td>
              <td><?= (int) $row['orders'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>

  <article class="panel-card">
    <strong>Top products snapshot</strong>
    <div class="summary-list" style="margin-top: 10px;">
      <?php foreach (adminStoreFilter($dashboardTopProducts ?? []) as $row): ?>
        <div class="summary-row">
          <div>
            <strong><?= e($row['name']) ?></strong>
            <span><?= e(adminStoreLabel($row['store_id'])) ?></span>
          </div>
          <span class="badge badge-green"><?= (int) $row['sold'] ?> sold</span>
        </div>
      <?php endforeach; ?>
    </div>
  </article>
</section>

