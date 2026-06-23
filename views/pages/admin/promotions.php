<?php $codes = adminData()['promotions']; $banners = adminData()['banners']; ?>
<div class="page-head">
  <div>
    <p class="eyebrow">Marketing</p>
    <h2>Promotions</h2>
    <p>Manage discount codes and the homepage banner rail.</p>
  </div>
</div>

<section class="grid-2">
  <form method="POST" action="<?= adminRoute('promotions') ?>" class="form-card form-grid grid-2" data-admin-validate>
    <?= csrfField() ?>
    <input type="hidden" name="action" value="save_promotion">
    <div class="field">
      <span>Code</span>
      <input type="text" name="code" required placeholder="MONSOON10">
    </div>
    <div class="field">
      <span>Type</span>
      <select name="type">
        <option value="percentage">Percentage</option>
        <option value="flat">Flat</option>
      </select>
    </div>
    <div class="field">
      <span>Value</span>
      <input type="text" name="value" required placeholder="10% or 500">
    </div>
    <div class="field">
      <span>Expiry</span>
      <input type="text" name="expiry" required placeholder="2026-07-15">
    </div>
    <div class="field">
      <span>Usage limit</span>
      <input type="number" name="usage_limit" required min="1" value="100">
    </div>
    <button type="submit" class="btn btn-primary">Save code draft</button>
  </form>

  <article class="table-card">
    <div class="table-shell">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Type</th>
            <th>Value</th>
            <th>Expiry</th>
            <th>Usage</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($codes as $row): ?>
            <tr>
              <td><?= e($row['code']) ?></td>
              <td><?= e($row['type']) ?></td>
              <td><?= e($row['value']) ?></td>
              <td><?= e($row['expiry']) ?></td>
              <td><?= (int) $row['used'] ?> / <?= (int) $row['usage_limit'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>
</section>

<section class="table-card" style="margin-top: 14px;">
  <div class="table-toolbar">
    <div>
      <strong>Homepage banners</strong>
      <p class="table-subtitle">Banner manager for the storefront homepage.</p>
    </div>
  </div>
  <div class="table-shell">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Headline</th>
          <th>Subhead</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($banners as $row): ?>
          <tr>
            <td><?= e($row['headline']) ?></td>
            <td><?= e($row['subhead']) ?></td>
            <td><span class="<?= adminStatusBadgeClass($row['status']) ?>"><?= e($row['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
