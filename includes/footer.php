</main>

<footer class="bg-neutral-900 text-neutral-400 border-t border-neutral-800 mt-auto">
  <div class="px-6 sm:px-10 py-12 grid grid-cols-1 md:grid-cols-4 gap-8">
    <div>
      <h3 class="text-white font-display font-black text-xl tracking-widest mb-4">SHOEBOX</h3>
      <p class="text-xs leading-relaxed">Kerala's premium sneaker vault. Authentic pairs, express regional delivery, and boutique experiences across Kochi, Kozhikode & Thrissur.</p>
    </div>
    <div>
      <h4 class="text-white text-xs font-bold uppercase tracking-widest mb-4">Navigate</h4>
      <ul class="space-y-2 text-xs">
        <li><a href="<?= url('shop.php') ?>" class="hover:text-[#FF3B00]">Shop All</a></li>
        <li><a href="<?= url('sale.php') ?>" class="hover:text-[#FF3B00]">Sale</a></li>
        <li><a href="<?= url('stores.php') ?>" class="hover:text-[#FF3B00]">Stores</a></li>
        <li><a href="<?= url('track.php') ?>" class="hover:text-[#FF3B00]">Track Order</a></li>
      </ul>
    </div>
    <div>
      <h4 class="text-white text-xs font-bold uppercase tracking-widest mb-4">Contact</h4>
      <ul class="space-y-2 text-xs">
        <li>WhatsApp: +91 90796 44290</li>
        <li>concierge@shoeboxkerala.com</li>
        <li>Lulu Mall, Kochi</li>
      </ul>
    </div>
    <div>
      <h4 class="text-white text-xs font-bold uppercase tracking-widest mb-4">Newsletter</h4>
      <form action="<?= url('api/newsletter.php') ?>" method="POST" class="flex gap-2">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="email" name="email" required placeholder="your@email.com" class="flex-1 bg-neutral-800 border border-neutral-700 text-white text-xs px-3 py-2 focus:outline-none focus:border-[#FF3B00]">
        <button type="submit" class="bg-[#FF3B00] text-white text-xs font-bold px-4 py-2 uppercase tracking-wider hover:bg-orange-600">Join</button>
      </form>
    </div>
  </div>
  <div class="border-t border-neutral-800 px-6 py-4 text-center text-[10px] tracking-widest uppercase">
    © <?= date('Y') ?> Shoebox Kerala. All rights reserved.
  </div>
</footer>

</div>

<!-- Cart Drawer -->
<div id="cart-overlay" class="fixed inset-0 bg-black/50 z-[60] hidden" onclick="closeCart()"></div>
<div id="cart-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-white z-[70] transform translate-x-full transition-transform duration-300 flex flex-col shadow-2xl">
  <div class="flex items-center justify-between p-5 border-b border-[#E8E5DF]">
    <h2 class="font-display font-black text-lg uppercase tracking-widest">Your Bag</h2>
    <button onclick="closeCart()" class="text-neutral-400 hover:text-black">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>
  <div id="cart-content" class="flex-1 overflow-y-auto p-5">
    <?php include __DIR__ . '/cart-drawer.php'; ?>
  </div>
</div>

<?php
$comparePoolJson = json_encode(array_values($comparePool ?? []), JSON_UNESCAPED_SLASHES);
$compareActiveJson = json_encode(array_values(getCompare()), JSON_UNESCAPED_SLASHES);
?>
<script>
  window.SHOEBOX_COMPARE = {
    pool: <?= $comparePoolJson ?: '[]' ?>,
    active: <?= $compareActiveJson ?: '[]' ?>
  };
</script>

<!-- Compare Modal -->
<div id="compare-overlay" class="fixed inset-0 bg-black/60 z-[80] hidden" onclick="closeCompareModal()"></div>
<div id="compare-modal" class="fixed inset-0 z-[90] hidden items-center justify-center px-4 py-6">
  <div class="bg-white w-full max-w-5xl max-h-[88vh] overflow-hidden shadow-2xl border border-[#E8E5DF] flex flex-col">
    <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-[#E8E5DF]">
      <div>
        <p class="text-[9px] font-mono uppercase tracking-[0.35em] text-neutral-400">Compare in place</p>
        <h2 class="font-display font-black text-xl uppercase tracking-widest">Compare sneakers</h2>
      </div>
      <button type="button" class="text-neutral-400 hover:text-black" onclick="closeCompareModal()" aria-label="Close compare modal">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div id="compare-modal-body" class="grid lg:grid-cols-[minmax(0,1fr)_280px] gap-0 overflow-y-auto">
      <div class="p-5 sm:p-6 border-b lg:border-b-0 lg:border-r border-[#E8E5DF]">
        <div id="compare-primary" class="grid sm:grid-cols-[220px_minmax(0,1fr)] gap-5 items-start"></div>
        <div class="mt-6">
          <h3 class="text-[10px] font-mono font-bold uppercase tracking-[0.35em] text-neutral-400 mb-3">Selected compare pair</h3>
          <div id="compare-active-list" class="grid sm:grid-cols-2 gap-4"></div>
        </div>
      </div>
      <aside class="p-5 sm:p-6 bg-[#FAF9F5]">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-xs font-black uppercase tracking-widest">Add another product</h3>
          <span id="compare-count" class="text-[10px] font-mono text-neutral-500"></span>
        </div>
        <div id="compare-pool" class="space-y-3"></div>
      </aside>
    </div>
  </div>
</div>

<!-- Mobile bottom nav -->
<nav class="md:hidden fixed bottom-0 inset-x-0 bg-neutral-900 border-t border-neutral-800 z-40 flex justify-around py-2">
  <a href="<?= url('index.php') ?>" class="flex flex-col items-center text-[9px] uppercase tracking-wider <?= $activeNav === 'home' ? 'text-[#FF3B00]' : 'text-neutral-500' ?>">
    <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    Home
  </a>
  <a href="<?= url('shop.php') ?>" class="flex flex-col items-center text-[9px] uppercase tracking-wider <?= $activeNav === 'shop' ? 'text-[#FF3B00]' : 'text-neutral-500' ?>">
    <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
    Shop
  </a>
  <a href="<?= url('brands.php') ?>" class="flex flex-col items-center text-[9px] uppercase tracking-wider <?= $activeNav === 'brands' ? 'text-[#FF3B00]' : 'text-neutral-500' ?>">
    <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    Brands
  </a>
  <a href="<?= url('sale.php') ?>" class="flex flex-col items-center text-[9px] uppercase tracking-wider <?= $activeNav === 'sale' ? 'text-[#FF3B00]' : 'text-neutral-500' ?>">
    <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
    Sale
  </a>
  <a href="<?= url('about.php') ?>" class="flex flex-col items-center text-[9px] uppercase tracking-wider <?= $activeNav === 'about' ? 'text-[#FF3B00]' : 'text-neutral-500' ?>">
    <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    FAQ
  </a>
</nav>

<script src="<?= asset('js/app.js') ?>?v=<?= time() ?>"></script>
<script>window.SHOEBOX = { baseUrl: '<?= url('') ?>', csrf: '<?= e(csrfToken()) ?>' };</script>
</body>
</html>
