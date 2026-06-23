<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Checkout';
$activeNav = '';
$cart = getCart();
$subtotal = cartSubtotal();
$freeDelivery = isFreeDelivery();
$shipping = shippingFee();
$total = cartTotal();
$step = $_SESSION['checkout_step'] ?? 'shopping';
$user = currentUser();
$savedDelivery = $_SESSION['delivery'] ?? [];

$delivery = [
    'full_name' => $savedDelivery['full_name'] ?? ($user['full_name'] ?? ''),
    'phone' => $savedDelivery['phone'] ?? ($user['phone'] ?? ''),
    'email' => $savedDelivery['email'] ?? ($user['email'] ?? ''),
    'district' => $savedDelivery['district'] ?? ($user['district'] ?? 'Kochi (Ernakulam)'),
    'street_address' => $savedDelivery['street_address'] ?? ($user['street_address'] ?? ''),
    'pincode' => $savedDelivery['pincode'] ?? ($user['pincode'] ?? ''),
];

if ($step === 'payment' && empty($savedDelivery)) {
    $step = 'delivery';
}

if ($step === 'done' && empty($_SESSION['placed_order_id'])) {
    $step = 'shopping';
}

require __DIR__ . '/includes/header.php';
?>

<section class="px-6 sm:px-10 py-10">
  <div class="max-w-6xl mx-auto">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-8">
      <div>
        <p class="text-[10px] font-mono text-[#FF3B00] uppercase tracking-[0.35em] mb-2">Secure checkout</p>
        <h1 class="font-display font-black text-3xl sm:text-4xl uppercase tracking-widest">Checkout</h1>
      </div>
      <a href="<?= url('shop.php') ?>" class="text-xs font-bold uppercase tracking-widest text-neutral-500 hover:text-[#FF3B00]">Continue shopping</a>
    </div>

    <div class="grid lg:grid-cols-[minmax(0,1fr)_360px] gap-8 items-start">
      <div class="space-y-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <?php
          $steps = [
              'shopping' => 'Bag',
              'delivery' => 'Delivery',
              'payment' => 'Payment',
              'done' => 'Done',
          ];
          ?>
          <?php foreach ($steps as $key => $label): ?>
            <div class="border px-4 py-3 text-center <?= $step === $key ? 'border-black bg-white' : 'border-[#E8E5DF] bg-[#FAF9F5]' ?>">
              <p class="text-[9px] font-mono uppercase tracking-[0.35em] text-neutral-400 mb-1">Step</p>
              <p class="text-sm font-black uppercase"><?= e($label) ?></p>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if (empty($cart) && $step !== 'done'): ?>
          <div class="bg-white border border-[#E8E5DF] p-8 sm:p-10 text-center">
            <p class="text-neutral-400 text-sm mb-4">Your bag is empty.</p>
            <a href="<?= url('shop.php') ?>" class="inline-block bg-neutral-900 text-white text-xs font-bold px-6 py-3 uppercase tracking-widest hover:bg-[#FF3B00]">Browse Collection</a>
          </div>
        <?php elseif ($step === 'shopping'): ?>
          <div class="bg-white border border-[#E8E5DF] p-6 sm:p-8">
            <div class="flex items-center justify-between mb-5">
              <h2 class="font-display font-black text-xl uppercase tracking-widest">Your Bag</h2>
              <span class="text-xs font-mono text-neutral-500"><?= cartCount() ?> item(s)</span>
            </div>
            <div class="space-y-4">
              <?php foreach ($cart as $item): ?>
              <div class="flex gap-4 border-b border-dashed border-[#E8E5DF] pb-4 last:border-b-0 last:pb-0">
                <div class="w-20 h-20 bg-[#FAF9F5] flex items-center justify-center flex-shrink-0 <?= empty($item['image_url']) ? 'sneaker-mini' : '' ?>" data-color="<?= e($item['shoe_color']) ?>" data-accent="<?= e($item['accent_color']) ?>">
                  <?php if (!empty($item['image_url'])): ?>
                    <img src="<?= asset($item['image_url']) ?>" alt="<?= e($item['name']) ?>" class="w-full h-full object-contain p-1">
                  <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-[9px] font-mono text-neutral-400 uppercase"><?= e($item['brand']) ?></p>
                  <h3 class="text-sm font-bold truncate"><?= e($item['name']) ?></h3>
                  <?php if ($item['size']): ?><p class="text-[10px] text-neutral-500 mt-0.5">UK <?= (int)$item['size'] ?></p><?php endif; ?>
                  <div class="flex items-center justify-between mt-3">
                    <span class="text-xs text-neutral-500">Qty <?= (int)$item['quantity'] ?></span>
                    <span class="text-sm font-black"><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>

            <form action="<?= url('api/checkout.php') ?>" method="POST" class="mt-6">
              <?= csrfField() ?>
              <input type="hidden" name="step" value="delivery">
              <button type="submit" class="w-full bg-neutral-900 hover:bg-[#FF3B00] text-white text-xs font-black py-4 uppercase tracking-widest transition-colors">Continue to Delivery</button>
            </form>
          </div>
        <?php elseif ($step === 'delivery'): ?>
          <div class="bg-white border border-[#E8E5DF] p-6 sm:p-8">
            <div class="flex items-center justify-between mb-5">
              <h2 class="font-display font-black text-xl uppercase tracking-widest">Delivery Details</h2>
              <span class="text-[10px] font-bold uppercase tracking-widest text-neutral-500">Auto-saved</span>
            </div>

            <form action="<?= url('api/checkout.php') ?>" method="POST" class="space-y-3">
              <?= csrfField() ?>
              <input type="hidden" name="step" value="payment">
              <input type="text" name="full_name" required placeholder="Full Name" value="<?= e($delivery['full_name']) ?>" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-black">
              <input type="tel" name="phone" required placeholder="Phone (+91)" value="<?= e($delivery['phone']) ?>" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-black">
              <input type="email" name="email" required placeholder="Email" value="<?= e($delivery['email']) ?>" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-black">
              <select name="district" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-black">
                <?php foreach (DISTRICTS as $d): ?>
                <option value="<?= e($d) ?>" <?= $delivery['district'] === $d ? 'selected' : '' ?>><?= e($d) ?></option>
                <?php endforeach; ?>
              </select>
              <textarea name="street_address" required rows="3" placeholder="Street Address" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-black"><?= e($delivery['street_address']) ?></textarea>
              <input type="text" name="pincode" placeholder="PIN Code" value="<?= e($delivery['pincode']) ?>" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-black">
              <button type="submit" class="w-full bg-neutral-900 hover:bg-[#FF3B00] text-white text-xs font-black py-4 uppercase tracking-widest mt-4">Continue to Payment</button>
            </form>
          </div>
        <?php elseif ($step === 'payment'): ?>
          <?php $deliverySession = $_SESSION['delivery'] ?? []; ?>
          <div class="space-y-6">
            <div class="bg-white border border-[#E8E5DF] p-6 sm:p-8">
              <div class="flex items-center justify-between mb-4">
                <h2 class="font-display font-black text-xl uppercase tracking-widest">Payment</h2>
                <form action="<?= url('api/checkout.php') ?>" method="POST">
                  <?= csrfField() ?>
                  <input type="hidden" name="step" value="delivery">
                  <button type="submit" class="text-[10px] font-bold uppercase tracking-widest text-neutral-500 hover:text-[#FF3B00]">Edit delivery details</button>
                </form>
              </div>

              <div class="bg-[#FAF9F5] border border-[#E8E5DF] p-4 text-xs text-neutral-600 mb-4">
                <p class="font-bold uppercase tracking-widest text-[10px] mb-2">Delivering to</p>
                <p class="font-semibold"><?= e($deliverySession['full_name'] ?? '') ?></p>
                <p><?= e($deliverySession['street_address'] ?? '') ?></p>
                <p><?= e(($deliverySession['district'] ?? '') . ' - ' . ($deliverySession['pincode'] ?? '')) ?></p>
              </div>

              <div class="border border-[#E8E5DF] p-3 mb-4">
                <?php $delivery = $deliverySession; ?>
                <?php include __DIR__ . '/includes/upi-payment.php'; ?>
              </div>

              <form action="<?= url('api/checkout.php') ?>" method="POST" class="space-y-3 border-t border-dashed border-[#E8E5DF] pt-4">
                <?= csrfField() ?>
                <input type="hidden" name="step" value="place">
                <input type="hidden" name="payment_method" value="cod">
                <div class="text-xs text-neutral-500">
                  Delivering to: <strong><?= e($deliverySession['full_name'] ?? '') ?></strong>, <?= e($deliverySession['district'] ?? '') ?>
                </div>
                <div class="flex justify-between font-black text-sm"><span>Order Total</span><span><?= formatPrice($total) ?></span></div>
                <button type="submit" class="w-full border-2 border-neutral-900 hover:bg-neutral-900 hover:text-white text-neutral-900 text-xs font-black py-3 uppercase tracking-widest transition-colors">
                  Place Order - Cash on Delivery
                </button>
              </form>
            </div>
          </div>
        <?php elseif ($step === 'done'): ?>
          <div class="bg-white border border-[#E8E5DF] p-8 sm:p-10 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="font-display font-black text-2xl uppercase mb-2">Order Placed</h2>
            <p class="text-sm text-neutral-500 mb-1">Tracking ID</p>
            <p class="font-mono font-bold text-[#FF3B00] mb-6"><?= e($_SESSION['placed_order_id'] ?? '') ?></p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
              <a href="<?= url('track.php?id=' . urlencode($_SESSION['placed_order_id'] ?? '')) ?>" class="inline-block bg-neutral-900 text-white text-xs font-bold px-6 py-3 uppercase tracking-widest hover:bg-[#FF3B00]">Track Order</a>
              <a href="<?= url('shop.php') ?>" class="inline-block border border-neutral-300 text-xs font-bold px-6 py-3 uppercase tracking-widest hover:border-black">Continue Shopping</a>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <aside class="bg-white border border-[#E8E5DF] p-6 sm:p-8 lg:sticky lg:top-24">
        <h2 class="font-display font-black text-xl uppercase tracking-widest mb-5">Order Summary</h2>

        <?php if (!empty($cart)): ?>
          <div class="space-y-4">
            <?php foreach ($cart as $item): ?>
              <div class="flex gap-3 border-b border-dashed border-[#E8E5DF] pb-4 last:border-b-0 last:pb-0">
                <div class="w-14 h-14 bg-[#FAF9F5] flex items-center justify-center flex-shrink-0 <?= empty($item['image_url']) ? 'sneaker-mini' : '' ?>" data-color="<?= e($item['shoe_color']) ?>" data-accent="<?= e($item['accent_color']) ?>">
                  <?php if (!empty($item['image_url'])): ?>
                    <img src="<?= asset($item['image_url']) ?>" alt="<?= e($item['name']) ?>" class="w-full h-full object-contain p-1">
                  <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-[9px] font-mono text-neutral-400 uppercase"><?= e($item['brand']) ?></p>
                  <p class="text-xs font-bold truncate"><?= e($item['name']) ?></p>
                  <?php if ($item['size']): ?><p class="text-[10px] text-neutral-500">UK <?= (int)$item['size'] ?></p><?php endif; ?>
                  <div class="flex justify-between items-center mt-1">
                    <span class="text-[10px] text-neutral-500">Qty <?= (int)$item['quantity'] ?></span>
                    <span class="text-sm font-black"><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="mt-6 space-y-2 text-sm border-t border-[#E8E5DF] pt-4">
            <div class="flex justify-between"><span class="text-neutral-500">Subtotal</span><span class="font-bold"><?= formatPrice($subtotal) ?></span></div>
            <div class="flex justify-between"><span class="text-neutral-500">Shipping</span><span class="font-bold"><?= $freeDelivery ? 'FREE' : formatPrice($shipping) ?></span></div>
            <?php if (!$freeDelivery): ?>
              <p class="text-[10px] text-[#FF3B00]">Add <?= formatPrice(FREE_SHIPPING_THRESHOLD - $subtotal) ?> more for free Kerala express shipping</p>
            <?php endif; ?>
            <div class="flex justify-between text-base font-black pt-2 border-t border-dashed"><span>Total</span><span><?= formatPrice($total) ?></span></div>
          </div>
        <?php elseif ($step === 'done' && !empty($_SESSION['placed_order_id'])): ?>
          <div class="bg-[#FAF9F5] border border-[#E8E5DF] p-5 text-center">
            <p class="text-[10px] font-mono uppercase tracking-[0.35em] text-neutral-400 mb-2">Order confirmed</p>
            <p class="font-mono font-bold text-[#FF3B00] mb-4"><?= e($_SESSION['placed_order_id']) ?></p>
            <a href="<?= url('track.php?id=' . urlencode($_SESSION['placed_order_id'])) ?>" class="inline-block bg-neutral-900 text-white text-xs font-bold px-5 py-3 uppercase tracking-widest hover:bg-[#FF3B00]">Track Order</a>
          </div>
        <?php else: ?>
          <div class="text-center py-6">
            <p class="text-neutral-400 text-sm">Your bag is empty.</p>
            <a href="<?= url('shop.php') ?>" class="inline-block mt-4 bg-neutral-900 text-white text-xs font-bold px-6 py-3 uppercase tracking-widest hover:bg-[#FF3B00]">Browse Collection</a>
          </div>
        <?php endif; ?>
      </aside>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
