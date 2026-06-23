<?php
$cart = getCart();
$subtotal = cartSubtotal();
$freeDelivery = isFreeDelivery();
$shipping = shippingFee();
$total = cartTotal();
?>

<?php if (empty($cart)): ?>
  <div class="text-center py-16">
    <p class="text-neutral-400 text-sm">Your bag is empty.</p>
    <a href="<?= url('shop.php') ?>" class="inline-block mt-4 bg-neutral-900 text-white text-xs font-bold px-6 py-3 uppercase tracking-widest hover:bg-[#FF3B00]">Browse Collection</a>
  </div>
<?php else: ?>

  <div class="space-y-4">
    <?php foreach ($cart as $item): ?>
    <div class="flex gap-3 border-b border-dashed border-[#E8E5DF] pb-4">
      <div class="w-16 h-16 bg-[#FAF9F5] flex items-center justify-center flex-shrink-0 <?= empty($item['image_url']) ? 'sneaker-mini' : '' ?>" data-color="<?= e($item['shoe_color']) ?>" data-accent="<?= e($item['accent_color']) ?>">
        <?php if (!empty($item['image_url'])): ?>
          <img src="<?= asset($item['image_url']) ?>" alt="<?= e($item['name']) ?>" class="w-full h-full object-contain p-1">
        <?php endif; ?>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-[9px] font-mono text-neutral-400 uppercase"><?= e($item['brand']) ?></p>
        <p class="text-xs font-bold truncate"><?= e($item['name']) ?></p>
        <?php if ($item['size']): ?><p class="text-[10px] text-neutral-500">UK <?= (int)$item['size'] ?></p><?php endif; ?>
        <div class="flex items-center justify-between mt-2">
          <div class="flex items-center gap-2">
            <form action="<?= url('api/cart.php') ?>" method="POST" class="inline">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="key" value="<?= e($item['key']) ?>">
              <input type="hidden" name="quantity" value="<?= max(0, $item['quantity'] - 1) ?>">
              <button type="submit" class="w-6 h-6 border border-neutral-300 text-xs">-</button>
            </form>
            <span class="text-xs font-mono"><?= (int)$item['quantity'] ?></span>
            <form action="<?= url('api/cart.php') ?>" method="POST" class="inline">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="key" value="<?= e($item['key']) ?>">
              <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
              <button type="submit" class="w-6 h-6 border border-neutral-300 text-xs">+</button>
            </form>
          </div>
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

  <a href="<?= url('checkout.php') ?>" class="mt-6 block w-full bg-neutral-900 hover:bg-[#FF3B00] text-white text-xs font-black py-4 text-center uppercase tracking-widest transition-colors">Go to Checkout</a>

<?php endif; ?>
