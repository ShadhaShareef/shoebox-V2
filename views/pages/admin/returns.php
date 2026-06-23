<?php $rows = adminStoreFilter(adminData()['returns']); ?>
<div class="page-head">
  <div>
    <p class="eyebrow">Sales</p>
    <h2>Returns and Refunds</h2>
    <p>Queue returns, approve or reject, and trigger finance workflows.</p>
  </div>
</div>

<section class="table-card">
  <div class="table-shell">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Return ID</th>
          <th>Order</th>
          <th>Customer</th>
          <th>Reason</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><?= e($row['id']) ?></td>
            <td><?= e($row['order_id']) ?></td>
            <td><?= e($row['customer']) ?></td>
            <td><?= e($row['reason']) ?></td>
            <td><span class="<?= adminStatusBadgeClass($row['status']) ?>"><?= e($row['status']) ?></span></td>
            <td class="toolbar-actions">
              <button class="btn btn-secondary btn-sm" type="button" data-inline-action data-inline-action-label="Return approved.">Approve</button>
              <button class="btn btn-secondary btn-sm" type="button" data-inline-action data-inline-action-label="Return rejected.">Reject</button>
              <button class="btn btn-primary btn-sm" type="button" data-inline-action data-inline-action-label="Refund started.">Refund</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
