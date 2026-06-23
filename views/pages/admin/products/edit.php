<?php $product = ($products ?? [])[0] ?? []; ?>
<div class="page-head">
  <div>
    <p class="eyebrow">Catalog</p>
    <h2>Edit Product</h2>
    <p>Update product metadata and keep the merchandising state current.</p>
  </div>
  <a class="btn btn-secondary btn-sm" href="<?= adminRoute('products/list') ?>">Back to list</a>
</div>

<form method="POST" action="<?= adminRoute('products/edit') ?>" enctype="multipart/form-data" class="form-card form-grid grid-2" data-admin-validate>
  <?= csrfField() ?>
  <input type="hidden" name="action" value="save_product">
  <div class="field">
    <span>Name</span>
    <input type="text" name="name" required value="<?= e($product['name'] ?? 'Air Force 1 07 Triple White') ?>">
  </div>
  <div class="field">
    <span>Brand</span>
    <select name="brand" required>
      <?php foreach (['Nike', 'Adidas Originals', 'New Balance', 'Puma', 'Asics'] as $brand): ?>
        <option <?= (($product['brand'] ?? '') === $brand) ? 'selected' : '' ?>><?= e($brand) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field">
    <span>Price</span>
    <input type="number" name="price" required min="0" step="1" value="<?= (int) ($product['price'] ?? 9695) ?>">
  </div>
  <div class="field">
    <span>Category</span>
    <input type="text" name="category" required value="<?= e($product['category'] ?? 'Classic') ?>">
  </div>
  <div class="field">
    <span>Gender</span>
    <input type="text" name="gender" required value="<?= e($product['gender'] ?? 'Unisex') ?>">
  </div>
  <div class="field">
    <span>Stock</span>
    <input type="number" name="stock" value="<?= (int) ($product['stock'] ?? 12) ?>">
  </div>
  <div class="field" style="grid-column: 1 / -1;">
    <span>Description</span>
    <textarea name="description" required><?= e($product['description'] ?? 'Product description goes here.') ?></textarea>
  </div>
  <div class="field" style="grid-column: 1 / -1;">
    <span>Replace image</span>
    <div class="image-upload">
      <img class="image-preview" src="<?= e($product['image'] ?? url('assets/images/products/airforce.png')) ?>" alt="Current product">
      <div>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
        <p class="table-subtitle">Inline validation runs on submit. TODO: write to storage and update image path in the database.</p>
      </div>
    </div>
  </div>
  <label class="field" style="grid-column: 1 / -1;">
    <span>Active</span>
    <select name="is_active">
      <option value="1">Yes</option>
      <option value="0">No</option>
    </select>
  </label>
  <div class="toolbar-actions" style="grid-column: 1 / -1;">
    <button type="submit" class="btn btn-primary">Save changes</button>
    <a href="<?= adminRoute('products/list') ?>" class="btn btn-secondary">Cancel</a>
  </div>
</form>
