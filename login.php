<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/admin.php';

if (currentAdmin()) {
    redirect('admin/');
}

if (currentUser()) {
    redirect('auth.php');
}

$pageTitle = 'Sign In';
$activeNav = 'auth';

require __DIR__ . '/includes/header.php';
?>

<section class="px-6 sm:px-10 py-14 max-w-6xl mx-auto">
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-stretch">
    <div class="bg-neutral-900 text-white p-8 sm:p-12 flex flex-col justify-between">
      <div>
        <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-[#FF3B00] mb-4">Welcome back</p>
        <h1 class="font-display font-black text-4xl sm:text-5xl uppercase tracking-widest leading-tight">Sign in to your account</h1>
        <p class="text-sm text-neutral-400 mt-4 max-w-md">Use this page for both customer and staff access. The backend will route you to the right portal automatically.</p>
      </div>
      <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs uppercase tracking-widest text-neutral-300">
        <div class="border border-neutral-800 p-4">Order history</div>
        <div class="border border-neutral-800 p-4">Faster checkout</div>
        <div class="border border-neutral-800 p-4">Staff portal</div>
      </div>
    </div>

    <div class="bg-white border border-[#E8E5DF] p-8 sm:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
      <div class="flex items-start justify-between gap-4 mb-6">
        <div>
          <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-neutral-400">Sign in</p>
          <h2 class="font-display font-black text-2xl uppercase tracking-widest mt-2">Account login</h2>
        </div>
        <a href="<?= url('register.php') ?>" class="text-[10px] font-bold uppercase tracking-widest text-[#FF3B00] hover:underline">Create account</a>
      </div>

      <?php foreach (getFlashes() as $flash): ?>
        <div class="mb-4 rounded border px-4 py-3 text-sm flash flash-<?= e($flash['type']) ?>">
          <strong><?= e($flash['title']) ?></strong>
          <?php if (!empty($flash['description'])): ?><p><?= e($flash['description']) ?></p><?php endif; ?>
        </div>
      <?php endforeach; ?>

      <form action="<?= url('api/auth.php') ?>" method="POST" class="space-y-4">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="return_to" value="login.php">
        <input type="email" name="email" required placeholder="Email" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-[#FF3B00]">
        <input type="password" name="password" required placeholder="Password" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-[#FF3B00]">
        <button type="submit" class="w-full bg-neutral-900 text-white text-xs font-black py-3 uppercase tracking-widest hover:bg-[#FF3B00] transition-colors">Sign In</button>
      </form>

      <p class="mt-4 text-xs text-neutral-500">
        Staff accounts can use the same sign-in page. If your email belongs to <span class="font-semibold">admin_users</span>, you will be sent to the admin panel automatically.
      </p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
