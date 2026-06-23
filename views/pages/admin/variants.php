<?php
$variantRows = adminStoreFilter([
    ['product' => 'Air Force 1 07 Triple White', 'sku' => 'NK-AF1-TW-07', 'size' => 7, 'color' => 'White', 'stock' => 4, 'store_id' => 'kochi'],
    ['product' => 'Air Force 1 07 Triple White', 'sku' => 'NK-AF1-TW-08', 'size' => 8, 'color' => 'White', 'stock' => 6, 'store_id' => 'kochi'],
    ['product' => 'Samba OG Gum Sole', 'sku' => 'ADI-SAMBA-GS-07', 'size' => 7, 'color' => 'Cream', 'stock' => 3, 'store_id' => 'kozhikode'],
    ['product' => 'Volt Elite V1 Obsidian Crimson', 'sku' => 'VOLT-ELITE-V1-09', 'size' => 9, 'color' => 'Black', 'stock' => 7, 'store_id' => 'thrissur'],
]);
?>
<div class="page-head">
  <div>
    <p class="eyebrow">Catalog</p>
    <h2>Variants and Stock</h2>
    <p>Per-size and color SKU grid with inline stock editing hooks.</p>
  </div>
</div>

<section class="table-card">
  <div class="table-toolbar">
    <div>
      <strong>Editable grid</strong>
      <p class="table-subtitle">Low stock rows are highlighted for quick attention.</p>
    </div>
    <div class="actions">
      <button class="btn btn-secondary btn-sm" type="button">Add row</button>
      <button class="btn btn-primary btn-sm" type="button">Save stock</button>
    </div>
  </div>
  <div class="table-shell">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>SKU</th>
          <th>Size</th>
          <th>Color</th>
          <th>Stock</th>
          <th>Store</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($variantRows as $row): ?>
          <tr class="<?= ((int) $row['stock'] <= 4) ? 'is-low-stock' : '' ?>">
            <td><?= e($row['product']) ?></td>
            <td><?= e($row['sku']) ?></td>
            <td>UK <?= (int) $row['size'] ?></td>
            <td><?= e($row['color']) ?></td>
            <td><input type="number" value="<?= (int) $row['stock'] ?>" min="0" class="table-input"></td>
            <td><?= e(adminStoreLabel($row['store_id'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
