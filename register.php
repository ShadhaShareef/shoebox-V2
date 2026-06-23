<?php
require_once __DIR__ . '/includes/init.php';

if (currentUser()) {
    redirect('auth.php');
}

$pageTitle = 'Create Account';
$activeNav = 'auth';

require __DIR__ . '/includes/header.php';
?>

<section class="px-6 sm:px-10 py-14 max-w-6xl mx-auto">
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-stretch">
    <div class="bg-[#FF3B00] text-white p-8 sm:p-12 flex flex-col justify-between">
      <div>
        <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-white/80 mb-4">Join Shoebox</p>
        <h1 class="font-display font-black text-4xl sm:text-5xl uppercase tracking-widest leading-tight">Create your account</h1>
        <p class="text-sm text-white/85 mt-4 max-w-md">Set up your profile once, then use it for orders, tracking, and loyalty rewards.</p>
      </div>
      <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs uppercase tracking-widest text-white/90">
        <div class="border border-white/20 p-4">Fast checkout</div>
        <div class="border border-white/20 p-4">Order tracking</div>
        <div class="border border-white/20 p-4">Rewards points</div>
      </div>
    </div>

    <div class="bg-white border border-[#E8E5DF] p-8 sm:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.04)]">
      <div class="flex items-start justify-between gap-4 mb-6">
        <div>
          <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-neutral-400">New customer</p>
          <h2 class="font-display font-black text-2xl uppercase tracking-widest mt-2">Create Account</h2>
        </div>
        <a href="<?= url('login.php') ?>" class="text-[10px] font-bold uppercase tracking-widest text-[#FF3B00] hover:underline">Sign in</a>
      </div>

      <form action="<?= url('api/auth.php') ?>" method="POST" class="space-y-4">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="return_to" value="register.php">
        <input type="text" name="full_name" required placeholder="Full Name" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-[#FF3B00]">
        <input type="email" name="email" required placeholder="Email" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-[#FF3B00]">
        <input type="tel" name="phone" placeholder="Phone" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-[#FF3B00]">
        <input type="password" name="password" required placeholder="Password" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-[#FF3B00]">
        <select name="district" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-[#FF3B00]">
          <?php foreach (DISTRICTS as $d): ?>
          <option><?= e($d) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="text" name="pincode" placeholder="PIN Code" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-[#FF3B00]">
        <button type="submit" class="w-full bg-[#FF3B00] text-white text-xs font-black py-3 uppercase tracking-widest">Create Account</button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
