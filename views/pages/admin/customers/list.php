<?php $rows = adminStoreFilter(adminData()['customers']); ?>
<div class="page-head">
  <div>
    <p class="eyebrow">People</p>
    <h2>Customers</h2>
    <p>Search the customer list and open the profile view for order history.</p>
  </div>
</div>

<section class="table-card">
  <div class="table-shell">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Orders</th>
          <th>Lifetime value</th>
          <th>Store</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><a href="<?= adminRoute('customers/detail') ?>" class="table-title"><?= e($row['name']) ?></a></td>
            <td><?= e($row['email']) ?></td>
            <td><?= e($row['phone']) ?></td>
            <td><?= (int) $row['orders'] ?></td>
            <td><?= formatPrice((float) $row['lifetime_value']) ?></td>
            <td><?= e(adminStoreLabel($row['store_id'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
