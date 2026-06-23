<?php
require_once __DIR__ . '/includes/init.php';

$user = currentUser();
if (!$user) {
    redirect('login.php');
}

$pageTitle = 'Account';
$activeNav = 'auth';
$orders = getUserOrders((int) $user['id']);
$currentTier = resolveMemberTier((int) $user['loyalty_points']);
$claimedFollow = hasLoyaltyClaim((int) $user['id'], 'instagram_follow');
$claimedVerify = hasLoyaltyClaim((int) $user['id'], 'phone_verify');

require __DIR__ . '/includes/header.php';
?>

<section class="px-6 sm:px-10 py-12 max-w-4xl mx-auto">
  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="font-display font-black text-2xl uppercase tracking-widest">Dashboard</h1>
      <p class="text-sm text-neutral-500 mt-1">Welcome, <?= e($user['full_name']) ?></p>
    </div>
    <form action="<?= url('api/auth.php') ?>" method="POST">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="text-xs font-bold uppercase tracking-widest text-neutral-500 hover:text-[#FF3B00]">Sign Out</button>
    </form>
  </div>

  <div class="bg-neutral-900 text-white p-6 mb-8 border-l-4 border-[#FF3B00]">
    <div class="flex justify-between items-start">
      <div>
        <span class="text-[9px] font-mono text-[#FF3B00] uppercase tracking-widest">Member Tier</span>
        <p class="font-display font-black text-xl uppercase mt-1"><?= e($currentTier['name']) ?></p>
      </div>
      <div class="text-right">
        <span class="text-[9px] font-mono text-neutral-400 uppercase">Loyalty Points</span>
        <p class="font-mono font-bold text-2xl text-[#FF3B00]"><?= (int) $user['loyalty_points'] ?></p>
      </div>
    </div>
    <div class="flex gap-3 mt-4 flex-wrap">
      <?php if (!$claimedFollow): ?>
      <form action="<?= url('api/auth.php') ?>" method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="claim_loyalty">
        <input type="hidden" name="claim_type" value="instagram_follow">
        <button type="submit" class="text-[10px] font-bold uppercase border border-neutral-600 px-3 py-1 hover:border-[#FF3B00]">+50 Follow Instagram</button>
      </form>
      <?php endif; ?>
      <?php if (!$claimedVerify): ?>
      <form action="<?= url('api/auth.php') ?>" method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="claim_loyalty">
        <input type="hidden" name="claim_type" value="phone_verify">
        <button type="submit" class="text-[10px] font-bold uppercase border border-neutral-600 px-3 py-1 hover:border-[#FF3B00]">+100 Verify Phone</button>
      </form>
      <?php endif; ?>
    </div>
  </div>

  <div class="bg-white border border-[#E8E5DF] p-6 mb-8">
    <h2 class="font-bold uppercase tracking-widest text-sm mb-4">Address Book</h2>
    <form action="<?= url('api/auth.php') ?>" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="update_profile">
      <input type="text" name="full_name" value="<?= e($user['full_name']) ?>" class="border border-[#E8E5DF] px-3 py-2 text-sm">
      <input type="tel" name="phone" value="<?= e($user['phone']) ?>" class="border border-[#E8E5DF] px-3 py-2 text-sm">
      <select name="district" class="border border-[#E8E5DF] px-3 py-2 text-sm">
        <?php foreach (DISTRICTS as $d): ?>
        <option <?= $user['district'] === $d ? 'selected' : '' ?>><?= e($d) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="pincode" value="<?= e($user['pincode']) ?>" class="border border-[#E8E5DF] px-3 py-2 text-sm">
      <textarea name="street_address" rows="2" class="sm:col-span-2 border border-[#E8E5DF] px-3 py-2 text-sm"><?= e($user['street_address']) ?></textarea>
      <button type="submit" class="sm:col-span-2 bg-neutral-900 text-white text-xs font-black py-3 uppercase tracking-widest w-full sm:w-auto px-8">Save Profile</button>
    </form>
  </div>

  <h2 class="font-display font-black text-xl uppercase tracking-widest mb-4">Your Orders</h2>
  <?php if (empty($orders)): ?>
  <p class="text-neutral-500 text-sm py-8 text-center">No orders yet. <a href="<?= url('shop.php') ?>" class="text-[#FF3B00] font-bold">Start shopping</a></p>
  <?php else: ?>
  <div class="space-y-4">
    <?php foreach ($orders as $order): ?>
    <div class="bg-white border border-[#E8E5DF] p-5">
      <div class="flex flex-wrap justify-between gap-2 mb-3">
        <span class="font-mono font-bold text-sm"><?= e($order['id']) ?></span>
        <span class="text-[10px] font-bold uppercase px-2 py-1 bg-neutral-100"><?= e($order['status']) ?></span>
      </div>
      <p class="text-xs text-neutral-500"><?= date('F j, Y', strtotime($order['created_at'])) ?> · <?= formatPrice((float) $order['total_amount']) ?></p>
      <a href="<?= url('track.php?id=' . urlencode($order['id'])) ?>" class="inline-block mt-3 text-[10px] font-bold uppercase text-[#FF3B00] tracking-widest">Track →</a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
