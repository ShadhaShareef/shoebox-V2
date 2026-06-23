<?php $rows = adminStoreFilter(adminData()['pickups']); ?>
<div class="page-head">
  <div>
    <p class="eyebrow">Stores</p>
    <h2>Pickup Orders</h2>
    <p>Queue store pickup orders and mark them ready for customer notification.</p>
  </div>
</div>

<section class="table-card">
  <div class="table-shell">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Customer</th>
          <th>Store</th>
          <th>Pickup code</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><?= e($row['order_id']) ?></td>
            <td><?= e($row['customer']) ?></td>
            <td><?= e(adminStoreLabel($row['store_id'])) ?></td>
            <td><?= e($row['pickup_code']) ?></td>
            <td><span class="<?= adminStatusBadgeClass($row['status']) ?>"><?= e($row['status']) ?></span></td>
            <td><button type="button" class="btn btn-primary btn-sm" data-inline-action data-inline-action-label="Customer notified that pickup is ready.">Ready for pickup</button></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
