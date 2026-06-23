<?php $detail = adminData()['orderDetail']; ?>
<div class="page-head">
  <div>
    <p class="eyebrow">Sales</p>
    <h2>Order Detail</h2>
    <p>Item breakdown, customer profile, shipping or pickup details, and status controls.</p>
  </div>
  <a class="btn btn-secondary btn-sm" href="<?= adminRoute('orders/list') ?>">Back to orders</a>
</div>

<section class="grid-2">
  <article class="panel-card">
    <strong>Order <?= e($detail['order_id']) ?></strong>
    <div class="summary-list" style="margin-top: 10px;">
      <div class="summary-row"><span>Customer</span><strong><?= e($detail['customer']['name']) ?></strong></div>
      <div class="summary-row"><span>Email</span><strong><?= e($detail['customer']['email']) ?></strong></div>
      <div class="summary-row"><span>Phone</span><strong><?= e($detail['customer']['phone']) ?></strong></div>
      <div class="summary-row"><span>Address</span><strong><?= e($detail['customer']['address']) ?></strong></div>
      <div class="summary-row"><span>Store</span><strong><?= e(adminStoreLabel($detail['customer']['store_id'])) ?></strong></div>
    </div>
  </article>

  <form method="POST" action="<?= adminRoute('orders/detail') ?>" class="form-card form-grid" data-admin-validate>
    <?= csrfField() ?>
    <input type="hidden" name="action" value="update_order">
    <div class="field">
      <span>Status</span>
      <select name="status" required>
        <?php foreach (['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $status): ?>
          <option><?= e($status) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <span>Refund action</span>
      <button class="btn btn-secondary" type="button" data-inline-action data-inline-action-label="Refund queued for finance review.">Trigger refund</button>
    </div>
    <div class="field">
      <span>Shipping method</span>
      <p class="table-subtitle"><?= e($detail['shipping']['method']) ?> via <?= e($detail['shipping']['carrier']) ?></p>
    </div>
    <div class="field">
      <span>Pickup store</span>
      <p class="table-subtitle"><?= e($detail['shipping']['pickup_store']) ?></p>
    </div>
    <button type="submit" class="btn btn-primary">Update status draft</button>
  </form>
</section>

<section class="table-card" style="margin-top: 14px;">
  <div class="table-toolbar">
    <div>
      <strong>Items</strong>
      <p class="table-subtitle">Full item breakdown for the order.</p>
    </div>
  </div>
  <div class="table-shell">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Item</th>
          <th>Qty</th>
          <th>Size</th>
          <th>Price</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($detail['items'] as $item): ?>
          <tr>
            <td><?= e($item['name']) ?></td>
            <td><?= (int) $item['qty'] ?></td>
            <td>UK <?= (int) $item['size'] ?></td>
            <td><?= formatPrice((float) $item['price']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
