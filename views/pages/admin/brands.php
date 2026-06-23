<?php $rows = adminData()['brands']; ?>
<div class="page-head">
  <div>
    <p class="eyebrow">Catalog</p>
    <h2>Brands</h2>
    <p>Manage storefront brand pages and keep brand storytelling consistent.</p>
  </div>
</div>

<section class="grid-2">
  <form method="POST" action="<?= adminRoute('brands') ?>" enctype="multipart/form-data" class="form-card form-grid" data-admin-validate>
    <?= csrfField() ?>
    <input type="hidden" name="action" value="save_brand">
    <div class="field">
      <span>Name</span>
      <input type="text" name="name" required placeholder="Nike">
    </div>
    <div class="field">
      <span>Description</span>
      <textarea name="description" required placeholder="Brand blurb for the storefront brand page."></textarea>
    </div>
    <div class="field">
      <span>Logo</span>
      <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
    </div>
    <button type="submit" class="btn btn-primary">Save brand draft</button>
  </form>

  <article class="table-card">
    <div class="table-toolbar">
      <div>
        <strong>Brand list</strong>
        <p class="table-subtitle">Shown on the storefront brand pages.</p>
      </div>
    </div>
    <div class="table-shell">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Logo</th>
            <th>Name</th>
            <th>Description</th>
            <th>Featured</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><img class="table-thumb" src="<?= e($row['logo']) ?>" alt="<?= e($row['name']) ?>"></td>
              <td><?= e($row['name']) ?></td>
              <td><?= e($row['description']) ?></td>
              <td><span class="<?= $row['featured'] ? 'badge badge-green' : 'badge badge-gray' ?>"><?= $row['featured'] ? 'Featured' : 'Hidden' ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>
</section>
