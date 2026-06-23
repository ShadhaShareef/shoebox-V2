<?php $detail = adminData()['customerDetail']; ?>
<div class="page-head">
  <div>
    <p class="eyebrow">People</p>
    <h2>Customer Detail</h2>
    <p>Contact info, loyalty snapshot, and order history in one view.</p>
  </div>
  <a class="btn btn-secondary btn-sm" href="<?= adminRoute('customers/list') ?>">Back to customers</a>
</div>

<section class="grid-2">
  <article class="panel-card">
    <strong><?= e($detail['profile']['name']) ?></strong>
    <div class="summary-list" style="margin-top: 10px;">
      <div class="summary-row"><span>Email</span><strong><?= e($detail['profile']['email']) ?></strong></div>
      <div class="summary-row"><span>Phone</span><strong><?= e($detail['profile']['phone']) ?></strong></div>
      <div class="summary-row"><span>City</span><strong><?= e($detail['profile']['city']) ?></strong></div>
      <div class="summary-row"><span>Points</span><strong><?= (int) $detail['profile']['points'] ?></strong></div>
    </div>
  </article>

  <form method="POST" action="<?= adminRoute('customers/detail') ?>" class="form-card form-grid" data-admin-validate>
    <?= csrfField() ?>
    <input type="hidden" name="action" value="save_customer_notes">
    <div class="field">
      <span>Notes</span>
      <textarea name="notes" required placeholder="Store note, sizing preference, VIP markers, etc."></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Save note draft</button>
  </form>
</section>

<section class="table-card" style="margin-top: 14px;">
  <div class="table-toolbar">
    <div>
      <strong>Order history</strong>
      <p class="table-subtitle">Recent orders for this customer.</p>
    </div>
  </div>
  <div class="table-shell">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Status</th>
          <th>Amount</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($detail['orders'] as $order): ?>
          <tr>
            <td><?= e($order['id']) ?></td>
            <td><span class="<?= adminStatusBadgeClass($order['status']) ?>"><?= e($order['status']) ?></span></td>
            <td><?= formatPrice((float) $order['amount']) ?></td>
            <td><?= e($order['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
