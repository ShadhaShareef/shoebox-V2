<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Sale';
$activeNav = 'sale';
$products = getProducts(null, null, null, 'lowToHigh', true);

require __DIR__ . '/includes/header.php';
?>

<section class="px-6 sm:px-10 py-8">
  <div class="bg-[#FF3B00] text-white p-8 mb-8">
    <h1 class="font-display font-black text-3xl uppercase tracking-widest">Sale Vault</h1>
    <p class="text-sm mt-2 opacity-90">Authenticated premium pairs at reduced Kerala pricing.</p>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <?php foreach ($products as $product): ?>
      <?php include __DIR__ . '/includes/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
  <?php if (empty($products)): ?>
  <p class="text-center text-neutral-500 py-16">No sale items right now. Check back soon.</p>
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
