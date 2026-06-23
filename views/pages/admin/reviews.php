<?php $rows = adminStoreFilter(adminData()['reviews']); ?>
<div class="page-head">
  <div>
    <p class="eyebrow">Sales</p>
    <h2>Reviews</h2>
    <p>Approve or reject the moderation queue and filter by rating or product.</p>
  </div>
</div>

<section class="table-card">
  <div class="table-toolbar">
    <div class="actions">
      <select>
        <option>All ratings</option>
        <option>5 stars</option>
        <option>4 stars</option>
        <option>3 stars</option>
      </select>
      <select>
        <option>All products</option>
        <option>Air Force 1 07 Triple White</option>
        <option>Palermo Cobalt Blue</option>
      </select>
    </div>
  </div>
  <div class="table-shell">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Product</th>
          <th>Rating</th>
          <th>Comment</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><?= e($row['customer']) ?></td>
            <td><?= e($row['product']) ?></td>
            <td><?= str_repeat('★', (int) $row['rating']) ?></td>
            <td><?= e($row['comment']) ?></td>
            <td><span class="<?= adminStatusBadgeClass($row['status']) ?>"><?= e($row['status']) ?></span></td>
            <td class="toolbar-actions">
              <button class="btn btn-secondary btn-sm" type="button" data-inline-action data-inline-action-label="Review approved.">Approve</button>
              <button class="btn btn-secondary btn-sm" type="button" data-inline-action data-inline-action-label="Review rejected.">Reject</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
