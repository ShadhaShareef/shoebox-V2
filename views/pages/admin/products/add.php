<div class="page-head">
  <div>
    <p class="eyebrow">Catalog</p>
    <h2>Add Product</h2>
    <p>Create a new item. Save is mocked for now, but validation and layout are in place.</p>
  </div>
  <a class="btn btn-secondary btn-sm" href="<?= adminRoute('products/list') ?>">Back to list</a>
</div>

<form method="POST" action="<?= adminRoute('products/add') ?>" enctype="multipart/form-data" class="form-card form-grid grid-2" data-admin-validate>
  <?= csrfField() ?>
  <input type="hidden" name="action" value="save_product">
  <div class="field">
    <span>Name</span>
    <input type="text" name="name" required placeholder="Air Force 1 07 Triple White">
  </div>
  <div class="field">
    <span>Brand</span>
    <select name="brand" required>
      <option value="">Select brand</option>
      <option>Nike</option>
      <option>Adidas Originals</option>
      <option>New Balance</option>
      <option>Puma</option>
      <option>Asics</option>
    </select>
  </div>
  <div class="field">
    <span>Price</span>
    <input type="number" name="price" required min="0" step="1" placeholder="9695">
  </div>
  <div class="field">
    <span>Category</span>
    <select name="category" required>
      <option value="">Select category</option>
      <option>Classic</option>
      <option>Trending</option>
      <option>New Arrival</option>
      <option>Sale</option>
      <option>Exclusive Drop</option>
    </select>
  </div>
  <div class="field">
    <span>Gender</span>
    <select name="gender" required>
      <option>Unisex</option>
      <option>Men</option>
      <option>Women</option>
      <option>Kids</option>
    </select>
  </div>
  <div class="field">
    <span>Stock units</span>
    <input type="number" name="stock" min="0" step="1" placeholder="12">
  </div>
  <div class="field" style="grid-column: 1 / -1;">
    <span>Description</span>
    <textarea name="description" required placeholder="Product description for the storefront."></textarea>
  </div>
  <div class="field" style="grid-column: 1 / -1;">
    <span>Images</span>
    <div class="image-upload">
      <img class="image-preview" src="<?= url('assets/images/products/airforce.png') ?>" alt="Preview">
      <div>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
        <p class="table-subtitle">Validated client-side and server-side before save. TODO: persist to `public/uploads/`.</p>
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
    <button type="submit" class="btn btn-primary">Save draft</button>
    <a href="<?= adminRoute('products/list') ?>" class="btn btn-secondary">Cancel</a>
  </div>
</form>
