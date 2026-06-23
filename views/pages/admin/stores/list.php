<?php $rows = adminData()['stores']; ?>
<div class="page-head">
  <div>
    <p class="eyebrow">Stores</p>
    <h2>Store Locations</h2>
    <p>Manage the three Kerala stores and their public-facing details.</p>
  </div>
  <a class="btn btn-primary btn-sm" href="<?= adminRoute('stores/edit') ?>">Edit store</a>
</div>

<section class="table-card">
  <div class="table-shell">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Photo</th>
          <th>Name</th>
          <th>Address</th>
          <th>Phone</th>
          <th>WhatsApp</th>
          <th>Hours</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><img class="table-thumb" src="<?= e($row['photo']) ?>" alt="<?= e($row['name']) ?>"></td>
            <td><?= e($row['name']) ?></td>
            <td><?= e($row['address']) ?></td>
            <td><?= e($row['phone']) ?></td>
            <td><?= e($row['whatsapp']) ?></td>
            <td><?= e($row['hours']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
