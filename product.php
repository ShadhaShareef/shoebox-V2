<?php
require_once __DIR__ . '/includes/init.php';

$id = (int) ($_GET['id'] ?? 0);
$product = getProductById($id);

if (!$product) {
    flash('warning', 'Product Not Found');
    redirect('shop.php');
}

$pageTitle = $product['name'];
$activeNav = 'shop';
$spec = getProductSpec($id);
$wishlisted = isWishlisted($id);
$comparePool = array_map('comparePayload', array_slice(array_values(array_filter(
    getProducts($product['brand'] ?? null, $product['category'] ?? null, null, 'popular')
)), 0, 8));
$comparePool = array_values(array_filter($comparePool, fn($item) => $item['id'] !== $id));

require __DIR__ . '/includes/header.php';
?>

<section class="px-6 sm:px-10 py-12">
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
    <div class="bg-[#FAF9F5] border border-[#E8E5DF] flex items-center justify-center min-h-[400px] <?= empty($product['image_url']) ? 'sneaker-graphic' : '' ?>" data-color="<?= e($product['shoe_color']) ?>" data-accent="<?= e($product['accent_color']) ?>">
      <?php if (!empty($product['image_url'])): ?>
        <img src="<?= asset($product['image_url']) ?>" alt="<?= e($product['name']) ?>" class="max-h-[350px] object-contain p-4">
      <?php endif; ?>
    </div>

    <div>
      <span class="text-[10px] font-mono text-neutral-400 uppercase tracking-widest"><?= e($product['brand']) ?></span>
      <h1 class="font-display font-black text-3xl uppercase mt-1 mb-4"><?= e($product['name']) ?></h1>

      <div class="flex items-center gap-3 mb-4">
        <span class="text-2xl font-black"><?= formatPrice($product['price']) ?></span>
        <?php if ($product['original_price']): ?>
        <span class="text-neutral-400 line-through"><?= formatPrice($product['original_price']) ?></span>
        <span class="text-[#FF3B00] text-sm font-bold">-<?= discountPercent($product['price'], $product['original_price']) ?>%</span>
        <?php endif; ?>
        <span class="text-amber-600 text-sm ml-auto">★ <?= number_format($product['rating'], 1) ?></span>
      </div>

      <span class="inline-block text-[9px] font-bold uppercase tracking-widest bg-neutral-100 px-2 py-1 mb-4"><?= e($product['category']) ?></span>

      <p class="text-sm text-neutral-600 leading-relaxed mb-6"><?= e($product['description']) ?></p>

      <?php if (!empty($product['sizes'])): ?>
      <form action="<?= url('api/cart.php') ?>" method="POST" class="mb-6" onsubmit="event.preventDefault(); openSizeModal(<?= $id ?>, <?= e(json_encode($product['sizes'])) ?>, this);">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= $id ?>">
        <input type="hidden" name="size" id="size-<?= $id ?>">
        <p class="text-[10px] font-mono uppercase tracking-widest text-neutral-500 mb-2">Select UK Size</p>
        <div class="flex flex-wrap gap-2 mb-4">
          <?php foreach ($product['sizes'] as $s): ?>
          <button type="button" onclick="document.getElementById('size-<?= $id ?>').value=<?= $s ?>; this.form.submit();" class="border border-[#E8E5DF] px-4 py-2 text-sm font-bold hover:border-black hover:bg-neutral-900 hover:text-white transition-colors">UK <?= $s ?></button>
          <?php endforeach; ?>
        </div>
        <button type="submit" class="w-full bg-neutral-900 hover:bg-[#FF3B00] text-white text-xs font-black py-4 uppercase tracking-widest">Add to Bag</button>
      </form>
      <?php else: ?>
      <form action="<?= url('api/cart.php') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= $id ?>">
        <button type="submit" class="w-full bg-neutral-900 hover:bg-[#FF3B00] text-white text-xs font-black py-4 uppercase tracking-widest">Add to Bag</button>
      </form>
      <?php endif; ?>

      <div class="flex gap-3 mt-4">
        <form action="<?= url('api/wishlist.php') ?>" method="POST">
          <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
          <input type="hidden" name="product_id" value="<?= $id ?>">
          <button type="submit" class="border border-[#E8E5DF] px-4 py-2 text-xs font-bold uppercase <?= $wishlisted ? 'text-[#FF3B00] border-[#FF3B00]' : '' ?>">
            <?= $wishlisted ? '♥ Wishlisted' : '♡ Wishlist' ?>
          </button>
        </form>
        <button
          type="button"
          data-compare-trigger
          data-compare-product="<?= e(json_encode(comparePayload($product), JSON_UNESCAPED_SLASHES)) ?>"
          class="border border-[#E8E5DF] px-4 py-2 text-xs font-bold uppercase hover:border-black"
        >
          Compare
        </button>
      </div>
    </div>
  </div>

  <?php if ($spec): ?>
  <div class="mt-16 border-t border-[#E8E5DF] pt-12">
    <h2 class="font-display font-black text-xl uppercase tracking-widest mb-6">Technical Specifications</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach (['material' => 'Material', 'midsole' => 'Midsole', 'weight' => 'Weight', 'resiliency' => 'Resiliency', 'culture_match' => 'Culture Match', 'traction' => 'Traction', 'origin' => 'Origin'] as $key => $label): ?>
      <div class="bg-white border border-[#E8E5DF] p-4">
        <span class="text-[9px] font-mono text-[#FF3B00] uppercase tracking-widest"><?= $label ?></span>
        <p class="text-sm mt-2"><?= e($spec[$key]) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</section>

<div id="size-modal" class="fixed inset-0 z-[80] items-center justify-center bg-black/60">
  <div class="bg-white p-6 max-w-sm w-full mx-4 shadow-2xl">
    <h3 class="font-display font-black uppercase tracking-widest mb-4">Select UK Size</h3>
    <div id="size-grid" class="flex flex-wrap gap-2 mb-6"></div>
    <div class="flex gap-3">
      <button type="button" onclick="closeSizeModal()" class="flex-1 border py-2 text-xs font-bold uppercase">Cancel</button>
      <button type="button" id="size-confirm" class="flex-1 bg-neutral-900 text-white py-2 text-xs font-bold uppercase">Add to Bag</button>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
