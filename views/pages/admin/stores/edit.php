<?php $store = (adminData()['stores'][0] ?? []); ?>
<div class="page-head">
  <div>
    <p class="eyebrow">Stores</p>
    <h2>Edit Store</h2>
    <p>Update contact details, hours, and the store photo.</p>
  </div>
</div>

<form method="POST" action="<?= adminRoute('stores/edit') ?>" enctype="multipart/form-data" class="form-card form-grid grid-2" data-admin-validate>
  <?= csrfField() ?>
  <input type="hidden" name="action" value="save_store">
  <div class="field">
    <span>Name</span>
    <input type="text" name="name" required value="<?= e($store['name'] ?? '') ?>">
  </div>
  <div class="field">
    <span>Phone</span>
    <input type="tel" name="phone" value="<?= e($store['phone'] ?? '') ?>">
  </div>
  <div class="field" style="grid-column: 1 / -1;">
    <span>Address</span>
    <textarea name="address" required><?= e($store['address'] ?? '') ?></textarea>
  </div>
  <div class="field">
    <span>WhatsApp</span>
    <input type="tel" name="whatsapp" value="<?= e($store['whatsapp'] ?? '') ?>">
  </div>
  <div class="field">
    <span>Hours</span>
    <input type="text" name="hours" value="<?= e($store['hours'] ?? '') ?>">
  </div>
  <div class="field" style="grid-column: 1 / -1;">
    <span>Photo</span>
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
  </div>
  <button type="submit" class="btn btn-primary">Save store draft</button>
</form>
