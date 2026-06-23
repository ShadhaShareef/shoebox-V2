<?php
$demoAdmins = adminCredentials();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
  <title>Shoebox Admin Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>?v=<?= time() ?>">
</head>
<body class="admin-body admin-login-body">
  <main class="login-screen">
    <section class="login-hero">
      <p class="eyebrow">Internal staff access</p>
      <h1>Shoebox Admin Panel</h1>
      <p>Manage products, orders, stores, staff, and promotions in one dense, laptop-first workspace.</p>
      <div class="login-points">
        <span>Role-gated access</span>
        <span>CSRF protected forms</span>
        <span>Prepared statement ready</span>
      </div>
    </section>

    <section class="login-card">
      <?php foreach (getFlashes() as $flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>">
          <strong><?= e($flash['title']) ?></strong>
          <?php if (!empty($flash['description'])): ?><p><?= e($flash['description']) ?></p><?php endif; ?>
        </div>
      <?php endforeach; ?>

      <div class="login-card-head">
        <div>
          <p class="eyebrow">Sign in</p>
          <h2>Admin login</h2>
        </div>
        <span class="badge badge-neutral">/admin/login</span>
      </div>

      <form method="POST" action="<?= adminRoute('login.php') ?>" class="form-stack" data-admin-validate>
        <?= csrfField() ?>
        <input type="hidden" name="action" value="login">
        <label>
          <span>Email</span>
          <input type="email" name="email" required placeholder="admin@shoebox.local">
        </label>
        <label>
          <span>Password</span>
          <input type="password" name="password" required placeholder="password" minlength="8">
        </label>
        <button type="submit" class="btn btn-primary">Sign in</button>
      </form>

      <div class="demo-panel">
        <h3>Demo credentials</h3>
        <ul>
          <?php foreach ($demoAdmins as $admin): ?>
            <li>
              <strong><?= e($admin['role']) ?></strong>
              <span><?= e($admin['email']) ?> / password</span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>
  </main>
  <script src="<?= url('public/js/admin.js') ?>?v=<?= time() ?>"></script>
</body>
</html>



