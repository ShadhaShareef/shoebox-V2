<?php $rows = adminData()['collections']; ?>
<div class="page-head">
  <div>
    <p class="eyebrow">Catalog</p>
    <h2>Collections</h2>
    <p>Create curated product groups and toggle homepage features.</p>
  </div>
</div>

<section class="grid-2">
  <form method="POST" action="<?= adminRoute('collections') ?>" enctype="multipart/form-data" class="form-card form-grid" data-admin-validate>
    <?= csrfField() ?>
    <input type="hidden" name="action" value="save_collection">
    <div class="field">
      <span>Name</span>
      <input type="text" name="name" required placeholder="Monsoon Ready Rotation">
    </div>
    <div class="field">
      <span>Featured toggle</span>
      <select name="featured">
        <option value="1">Featured</option>
        <option value="0">Hidden</option>
      </select>
    </div>
    <div class="field">
      <span>Products</span>
      <textarea name="products" required placeholder="Comma-separated product IDs"></textarea>
    </div>
    <div class="field">
      <span>Image</span>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
    </div>
    <button type="submit" class="btn btn-primary">Save collection draft</button>
  </form>

  <article class="table-card">
    <div class="table-toolbar">
      <div>
        <strong>Curated groups</strong>
        <p class="table-subtitle">Homepage-ready collections with featured toggles.</p>
      </div>
    </div>
    <div class="table-shell">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Products</th>
            <th>Featured</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><img class="table-thumb" src="<?= e($row['image']) ?>" alt="<?= e($row['name']) ?>"></td>
              <td><?= e($row['name']) ?></td>
              <td><?= (int) $row['products'] ?></td>
              <td><span class="<?= $row['featured'] ? 'badge badge-green' : 'badge badge-gray' ?>"><?= $row['featured'] ? 'Featured' : 'Hidden' ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>
</section>
