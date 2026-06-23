<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Track Order';
$activeNav = 'track';
$user = currentUser();
$orderId = trim($_GET['id'] ?? $_POST['id'] ?? '');
$verifyEmail = normalizeEmail($_GET['email'] ?? $_POST['email'] ?? '');
$order = null;
$accessDenied = false;

if ($orderId !== '') {
    $order = getOrderById($orderId);
    if ($order) {
        if (!canViewOrder($order, $user, $verifyEmail ?: null)) {
            $accessDenied = true;
            $order = null;
        }
    }
}

$statusLabels = [
    'placed' => 'Order Placed',
    'authenticated' => 'Authenticated',
    'packed' => 'Packed',
    'transit' => 'In Transit',
    'delivered' => 'Delivered',
];

require __DIR__ . '/includes/header.php';
?>

<section class="px-6 sm:px-10 py-12 max-w-3xl mx-auto">
  <h1 class="font-display font-black text-3xl uppercase tracking-widest mb-6 text-center">Track Your Order</h1>

  <form method="GET" class="flex flex-col sm:flex-row gap-2 mb-10 max-w-lg mx-auto">
    <input type="text" name="id" value="<?= e($orderId) ?>" required placeholder="SHBX-KL-123456" class="flex-1 border border-[#E8E5DF] px-4 py-3 text-sm font-mono focus:outline-none focus:border-black">
    <?php if (!$user): ?>
    <input type="email" name="email" value="<?= e($verifyEmail) ?>" required placeholder="Order email" class="flex-1 border border-[#E8E5DF] px-4 py-3 text-sm focus:outline-none focus:border-black">
    <?php endif; ?>
    <button type="submit" class="bg-neutral-900 text-white text-xs font-black px-6 py-3 uppercase tracking-widest hover:bg-[#FF3B00]">Track</button>
  </form>

  <?php if ($accessDenied): ?>
  <div class="text-center py-12 bg-white border border-[#E8E5DF] max-w-lg mx-auto">
    <p class="text-neutral-500 mb-2">We couldn't verify access to this order.</p>
    <p class="text-xs text-neutral-400">Enter the email address used at checkout, or sign in to your account.</p>
  </div>
  <?php elseif ($orderId && !$order): ?>
  <div class="text-center py-12 bg-white border border-[#E8E5DF]">
    <p class="text-neutral-500">No order found with ID <strong class="font-mono"><?= e($orderId) ?></strong></p>
  </div>
  <?php elseif ($order): ?>

  <div class="bg-white border border-[#E8E5DF] overflow-hidden">
    <div class="bg-neutral-900 text-white p-6">
      <div class="flex flex-wrap justify-between gap-4">
        <div>
          <span class="text-[9px] font-mono text-[#FF3B00] uppercase tracking-widest">Tracking ID</span>
          <p class="font-mono font-bold text-xl"><?= e($order['id']) ?></p>
        </div>
        <div class="text-right">
          <span class="text-[9px] font-mono text-neutral-400 uppercase">Status</span>
          <p class="font-bold uppercase text-[#FF3B00]"><?= e($statusLabels[$order['status']] ?? $order['status']) ?></p>
        </div>
      </div>
      <p class="text-sm text-neutral-400 mt-3"><?= e($order['courier']) ?></p>
    </div>

    <div class="p-6 border-b border-[#E8E5DF]">
      <h3 class="text-xs font-bold uppercase tracking-widest mb-3">Delivery To</h3>
      <p class="text-sm"><?= e($order['full_name']) ?></p>
      <p class="text-sm text-neutral-500"><?= e($order['street_address']) ?>, PIN <?= e($order['pincode']) ?></p>
      <p class="text-sm text-neutral-500"><?= e($order['district']) ?>, Kerala</p>
      <p class="text-sm text-neutral-500 mt-1"><?= e($order['phone']) ?></p>
    </div>

    <div class="p-6 border-b border-[#E8E5DF]">
      <h3 class="text-xs font-bold uppercase tracking-widest mb-3">Items</h3>
      <?php foreach ($order['items'] as $item): ?>
      <div class="flex justify-between text-sm py-2 border-b border-dashed border-[#E8E5DF] last:border-0">
        <span><?= e($item['name']) ?> <?= $item['size_uk'] ? '(UK ' . (int)$item['size_uk'] . ')' : '' ?> × <?= (int)$item['quantity'] ?></span>
        <span class="font-bold"><?= formatPrice((float)$item['unit_price'] * (int)$item['quantity']) ?></span>
      </div>
      <?php endforeach; ?>
      <div class="flex justify-between font-black mt-3 pt-3 border-t">
        <span>Total</span>
        <span><?= formatPrice((float)$order['total_amount']) ?></span>
      </div>
    </div>

    <div class="p-6">
      <h3 class="text-xs font-bold uppercase tracking-widest mb-6">Timeline</h3>
      <div class="relative pl-6 space-y-6">
        <div class="absolute left-2 top-2 bottom-2 w-0.5 bg-[#E8E5DF]"></div>
        <?php foreach ($order['timeline'] as $event):
          $isActive = $event['status'] === $order['status'];
          $isPast = array_search($event['status'], array_column($order['timeline'], 'status')) <= array_search($order['status'], array_column($order['timeline'], 'status'));
        ?>
        <div class="relative">
          <div class="absolute -left-4 w-4 h-4 rounded-full border-2 <?= $isPast ? 'bg-[#FF3B00] border-[#FF3B00]' : 'bg-white border-neutral-300' ?>"></div>
          <div class="<?= $isActive ? '' : 'opacity-60' ?>">
            <p class="text-[10px] font-mono text-[#FF3B00] uppercase"><?= e($statusLabels[$event['status']] ?? $event['status']) ?></p>
            <p class="text-xs text-neutral-400"><?= e($event['event_date']) ?> · <?= e($event['event_time']) ?></p>
            <p class="text-sm mt-1"><?= e($event['description']) ?></p>
            <p class="text-[10px] text-neutral-400 mt-1">📍 <?= e($event['location']) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
