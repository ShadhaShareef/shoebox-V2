<?php $rows = adminData()['staff']; ?>
<div class="page-head">
  <div>
    <p class="eyebrow">People</p>
    <h2>Staff and Roles</h2>
    <p>Add or edit staff accounts and assign role plus store scope.</p>
  </div>
</div>

<section class="grid-2">
  <form method="POST" action="<?= adminRoute('staff') ?>" class="form-card form-grid" data-admin-validate>
    <?= csrfField() ?>
    <input type="hidden" name="action" value="save_staff">
    <div class="field">
      <span>Name</span>
      <input type="text" name="name" required placeholder="Megha S.">
    </div>
    <div class="field">
      <span>Email</span>
      <input type="email" name="email" required placeholder="megha@shoebox.local">
    </div>
    <div class="field">
      <span>Role</span>
      <select name="role" required>
        <option value="admin">Admin</option>
        <option value="store_manager">Store Manager</option>
      </select>
    </div>
    <div class="field">
      <span>Store</span>
      <select name="store_id">
        <option value="">All stores</option>
        <option value="kochi">Kochi</option>
        <option value="kozhikode">Kozhikode</option>
        <option value="thrissur">Thrissur</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Save staff draft</button>
  </form>

  <article class="table-card">
    <div class="table-shell">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Store</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?= e($row['name']) ?></td>
              <td><?= e($row['email']) ?></td>
              <td><?= e(adminRoleLabel($row['role'])) ?></td>
              <td><?= e(adminStoreLabel($row['store_id'])) ?></td>
              <td><span class="<?= adminStatusBadgeClass($row['status']) ?>"><?= e($row['status']) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </article>
</section>
