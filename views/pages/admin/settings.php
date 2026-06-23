<?php $settings = adminData()['settings']; ?>
<div class="page-head">
  <div>
    <p class="eyebrow">System</p>
    <h2>Site Settings</h2>
    <p>Shipping rates, tax, free delivery threshold, and store pickup toggle.</p>
  </div>
</div>

<form method="POST" action="<?= adminRoute('settings') ?>" class="form-card form-grid grid-2" data-admin-validate>
  <?= csrfField() ?>
  <input type="hidden" name="action" value="save_settings">
  <div class="field">
    <span>Shipping rate</span>
    <input type="number" name="shipping_rate" required value="<?= (int) $settings['shipping_rate'] ?>">
  </div>
  <div class="field">
    <span>Tax rate</span>
    <input type="number" name="tax_rate" required value="<?= (int) $settings['tax_rate'] ?>">
  </div>
  <div class="field">
    <span>Free delivery threshold</span>
    <input type="number" name="free_delivery_threshold" required value="<?= (int) $settings['free_delivery_threshold'] ?>">
  </div>
  <div class="field">
    <span>Store pickup</span>
    <select name="pickup_enabled">
      <option value="1" <?= $settings['pickup_enabled'] ? 'selected' : '' ?>>Enabled</option>
      <option value="0" <?= !$settings['pickup_enabled'] ? 'selected' : '' ?>>Disabled</option>
    </select>
  </div>
  <button type="submit" class="btn btn-primary">Save settings draft</button>
</form>
