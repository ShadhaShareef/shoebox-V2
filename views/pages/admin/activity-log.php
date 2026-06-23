<?php $rows = adminData()['activity']; ?>
<div class="page-head">
  <div>
    <p class="eyebrow">System</p>
    <h2>Activity Log</h2>
    <p>Audit trail of who changed what and when.</p>
  </div>
</div>

<section class="table-card">
  <div class="table-shell">
    <table class="admin-table">
      <thead>
        <tr>
          <th>User</th>
          <th>Action</th>
          <th>Target</th>
          <th>When</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><?= e($row['user']) ?></td>
            <td><?= e($row['action']) ?></td>
            <td><?= e($row['target']) ?></td>
            <td><?= e($row['when']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
