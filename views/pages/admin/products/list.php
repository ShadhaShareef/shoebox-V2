<?php
$rows = adminStoreFilter($products ?? []);
?>

<div class="page-head">
  <div>
    <p class="eyebrow">Catalog</p>
    <h2>Products</h2>
    <p>Search, sort, bulk manage, and keep product states clean.</p>
  </div>
  <div class="toolbar-actions">
    <a class="btn btn-secondary btn-sm" href="<?= adminRoute('variants') ?>">Variants</a>
    <a class="btn btn-primary btn-sm" href="<?= adminRoute('products/add') ?>">Add product</a>
  </div>
</div>

<section class="table-card">
  <div class="table-toolbar">
    <div class="actions">
      <select>
        <option>All statuses</option>
        <option>Active</option>
        <option>Low stock</option>
        <option>Inactive</option>
      </select>
      <button class="btn btn-secondary btn-sm" type="button">Bulk delete</button>
      <button class="btn btn-secondary btn-sm" type="button">Activate</button>
      <button class="btn btn-secondary btn-sm" type="button">Deactivate</button>
    </div>
    <div class="actions">
      <span class="badge badge-neutral"><?= count($rows) ?> items</span>
    </div>
  </div>
  <div class="table-shell">
    <table class="admin-table" data-admin-table>
      <thead>
        <tr>
          <th></th>
          <th data-sort>Product</th>
          <th data-sort>Brand</th>
          <th data-sort>Category</th>
          <th data-sort>Price</th>
          <th data-sort>Stock</th>
          <th data-sort>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><img class="table-thumb" src="<?= e($row['image']) ?>" alt="<?= e($row['name']) ?>"></td>
            <td>
              <div class="table-title"><?= e($row['name']) ?></div>
              <div class="table-subtitle"><?= e($row['gender']) ?></div>
            </td>
            <td><?= e($row['brand']) ?></td>
            <td><?= e($row['category']) ?></td>
            <td><?= formatPrice((float) $row['price']) ?></td>
            <td><?= (int) $row['stock'] ?></td>
            <td><span class="<?= adminStatusBadgeClass($row['status']) ?>"><?= e($row['status']) ?></span></td>
            <td class="toolbar-actions">
              <a class="btn btn-secondary btn-sm" href="<?= adminRoute('products/edit') ?>">Edit</a>
              <button type="button" class="btn btn-secondary btn-sm">Stock</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
