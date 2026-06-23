<?php $rows = adminStoreFilter($orders ?? []); ?>
<div class="page-head">
  <div>
    <p class="eyebrow">Sales</p>
    <h2>Orders</h2>
    <p>Filter by status, search by customer, and jump into order detail.</p>
  </div>
  <div class="toolbar-actions">
    <a class="btn btn-secondary btn-sm" href="<?= adminRoute('returns') ?>">Returns</a>
    <a class="btn btn-primary btn-sm" href="<?= adminRoute('orders/detail') ?>">Open sample order</a>
  </div>
</div>

<section class="table-card">
  <div class="table-toolbar">
    <div class="actions">
      <select>
        <option>All statuses</option>
        <option>Pending</option>
        <option>Processing</option>
        <option>Shipped</option>
        <option>Delivered</option>
        <option>Cancelled</option>
      </select>
    </div>
  </div>
  <div class="table-shell">
    <table class="admin-table" data-admin-table>
      <thead>
        <tr>
          <th data-sort>Order ID</th>
          <th data-sort>Customer</th>
          <th data-sort>Store</th>
          <th data-sort>Status</th>
          <th data-sort>Channel</th>
          <th data-sort>Amount</th>
          <th data-sort>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><?= e($row['id']) ?></td>
            <td>
              <div class="table-title"><?= e($row['customer']) ?></div>
              <div class="table-subtitle"><?= e($row['email']) ?></div>
            </td>
            <td><?= e(adminStoreLabel($row['store_id'])) ?></td>
            <td><span class="<?= adminStatusBadgeClass($row['status']) ?>"><?= e($row['status']) ?></span></td>
            <td><?= e($row['channel'] ?? '—') ?></td>
            <td><?= formatPrice((float) $row['amount']) ?></td>
            <td><?= e($row['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
