<?php $rows = adminStoreFilter(adminData()['inventory']); ?>
<div class="page-head">
  <div>
    <p class="eyebrow">Stores</p>
    <h2>Store Inventory</h2>
    <p>Track per-store stock and prepare transfers between locations.</p>
  </div>
</div>

<section class="grid-2">
  <article class="table-card">
    <div class="table-shell">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Size</th>
            <th>Stock</th>
            <th>Store</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr class="<?= $row['low_stock'] ? 'is-low-stock' : '' ?>">
              <td><?= e($row['product']) ?></td>
              <td><?= e($row['sku']) ?></td>
              <td><?= e($row['size']) ?></td>
              <td><?= (int) $row['stock'] ?></td>
              <td><?= e(adminStoreLabel($row['store_id'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>

  <form method="POST" action="<?= adminRoute('stores/inventory') ?>" class="form-card form-grid" data-admin-validate>
    <?= csrfField() ?>
    <input type="hidden" name="action" value="transfer_stock">
    <div class="field">
      <span>Product</span>
      <input type="text" name="product" required placeholder="Air Force 1 07 Triple White">
    </div>
    <div class="field">
      <span>From store</span>
      <select name="from_store" required>
        <option value="kochi">Kochi</option>
        <option value="kozhikode">Kozhikode</option>
        <option value="thrissur">Thrissur</option>
      </select>
    </div>
    <div class="field">
      <span>To store</span>
      <select name="to_store" required>
        <option value="kochi">Kochi</option>
        <option value="kozhikode">Kozhikode</option>
        <option value="thrissur">Thrissur</option>
      </select>
    </div>
    <div class="field">
      <span>Stock units</span>
      <input type="number" name="stock" required min="1" value="1">
    </div>
    <button type="submit" class="btn btn-primary">Queue transfer</button>
  </form>
</section>
