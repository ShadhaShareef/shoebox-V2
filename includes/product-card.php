<?php
/** @var array $product */
$wishlisted = isWishlisted((int)$product['id']);
$inCompare = in_array((int)$product['id'], getCompare(), true);
?>
<div class="product-card group bg-white border border-[#E8E5DF] hover:border-neutral-900 transition-all flex flex-col">
  <div class="relative p-4">
    <div class="absolute top-3 left-3 z-10 flex flex-col gap-1">
      <?php if ($product['category'] === 'New Arrival'): ?>
      <span class="bg-[#FF3B00] text-white text-[8px] font-bold px-2 py-0.5 uppercase tracking-wider">New</span>
      <?php endif; ?>
      <?php if ($product['original_price']): ?>
      <span class="bg-neutral-900 text-white text-[8px] font-bold px-2 py-0.5 uppercase">-<?= discountPercent($product['price'], $product['original_price']) ?>%</span>
      <?php endif; ?>
    </div>
    <div class="flex gap-2 absolute top-3 right-3 z-10">
      <form action="<?= url('api/wishlist.php') ?>" method="POST" class="inline">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
        <button type="submit" class="w-8 h-8 bg-white/80 border border-[#E8E5DF] flex items-center justify-center hover:border-[#FF3B00] <?= $wishlisted ? 'text-[#FF3B00]' : 'text-neutral-400' ?>">
          <svg class="w-4 h-4" fill="<?= $wishlisted ? 'currentColor' : 'none' ?>" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </button>
      </form>
      <button
        type="button"
        data-compare-trigger
        data-compare-product="<?= e(json_encode(comparePayload($product), JSON_UNESCAPED_SLASHES)) ?>"
        class="w-8 h-8 bg-white/80 border border-[#E8E5DF] flex items-center justify-center hover:border-[#FF3B00] <?= $inCompare ? 'text-[#FF3B00]' : 'text-neutral-400' ?>"
        title="Open compare"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
      </button>
    </div>
    <a href="<?= url('product.php?id=' . $product['id']) ?>" class="block w-full h-48 bg-[#FAF9F5] flex items-center justify-center <?= empty($product['image_url']) ? 'sneaker-graphic' : '' ?>" data-color="<?= e($product['shoe_color']) ?>" data-accent="<?= e($product['accent_color']) ?>">
      <?php if (!empty($product['image_url'])): ?>
        <img src="<?= asset($product['image_url']) ?>" alt="<?= e($product['name']) ?>" class="h-full w-full object-contain p-2 transition-transform duration-300 group-hover:scale-105">
      <?php endif; ?>
    </a>
  </div>
  <div class="p-4 pt-0 flex-1 flex flex-col">
    <span class="text-[9px] font-mono text-neutral-400 uppercase tracking-widest"><?= e($product['brand']) ?></span>
    <a href="<?= url('product.php?id=' . $product['id']) ?>" class="text-sm font-black uppercase mt-0.5 hover:text-[#FF3B00] line-clamp-2"><?= e($product['name']) ?></a>
    <div class="flex items-center gap-2 mt-2">
      <span class="text-sm font-black"><?= formatPrice($product['price']) ?></span>
      <?php if ($product['original_price']): ?>
      <span class="text-xs text-neutral-400 line-through"><?= formatPrice($product['original_price']) ?></span>
      <?php endif; ?>
      <span class="text-[10px] text-amber-600 ml-auto">★ <?= number_format($product['rating'], 1) ?></span>
    </div>
        <form action="<?= url('api/cart.php') ?>" method="POST" class="mt-auto pt-4" onsubmit="event.preventDefault(); openSizeModal(<?= (int)$product['id'] ?>, <?= e(json_encode($product['sizes'])) ?>, this);">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
      <input type="hidden" name="size" id="size-<?= (int)$product['id'] ?>">
      <button type="submit" class="w-full bg-neutral-900 hover:bg-[#FF3B00] text-white text-[10px] font-black py-2.5 uppercase tracking-widest transition-colors">Add to Bag</button>
    </form>
  </div>
</div>
