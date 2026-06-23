<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Shop';
$activeNav = 'shop';

$brand = $_GET['brand'] ?? 'All';
$category = $_GET['category'] ?? 'All';
$search = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'popular';
$wishlistOnly = isset($_GET['wishlist']);

$products = getProducts($brand, $category, $search, $sort);
if ($wishlistOnly) {
    $wl = getWishlist();
    $products = array_filter($products, fn($p) => in_array((int)$p['id'], $wl, true));
}
$brands = getBrands();
$categories = getCategories();
$compareIds = getCompare();
$comparePool = array_map('comparePayload', array_values($products));

require __DIR__ . '/includes/header.php';
?>

<section class="px-6 sm:px-10 py-8">
  <div class="flex flex-col lg:flex-row gap-8">
    <!-- Filters sidebar -->
    <aside class="lg:w-56 flex-shrink-0">
      <form method="GET" class="space-y-6 sticky top-24">
        <div>
          <label class="text-[10px] font-mono uppercase tracking-widest text-neutral-500 block mb-2">Search</label>
          <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search sneakers..." class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-black">
        </div>
        <div>
          <label class="text-[10px] font-mono uppercase tracking-widest text-neutral-500 block mb-2">Brand</label>
          <select name="brand" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="All">All Brands</option>
            <?php foreach ($brands as $b): ?>
            <option value="<?= e($b) ?>" <?= $brand === $b ? 'selected' : '' ?>><?= e($b) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-[10px] font-mono uppercase tracking-widest text-neutral-500 block mb-2">Category</label>
          <select name="category" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="All">All Categories</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= e($c) ?>" <?= $category === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="text-[10px] font-mono uppercase tracking-widest text-neutral-500 block mb-2">Sort</label>
          <select name="sort" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
            <option value="lowToHigh" <?= $sort === 'lowToHigh' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="highToLow" <?= $sort === 'highToLow' ? 'selected' : '' ?>>Price: High to Low</option>
          </select>
        </div>
        <button type="submit" class="w-full bg-neutral-900 text-white text-xs font-bold py-2 uppercase tracking-widest">Apply</button>
      </form>
    </aside>

    <!-- Product grid -->
    <div class="flex-1">
      <div class="flex items-center justify-between mb-6">
        <h1 class="font-display font-black text-2xl uppercase tracking-widest">
          <?= $wishlistOnly ? 'Wishlist' : 'The Vault' ?>
          <span class="text-neutral-400 text-sm font-normal ml-2">(<?= count($products) ?>)</span>
        </h1>
        <a href="<?= url('shop.php?wishlist=1') ?>" class="text-xs font-bold uppercase tracking-widest <?= $wishlistOnly ? 'text-[#FF3B00]' : 'text-neutral-500' ?>">♥ Wishlist</a>
      </div>

      <?php if (empty($products)): ?>
      <p class="text-neutral-500 text-center py-16">No products match your filters.</p>
      <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach ($products as $product): ?>
          <?php include __DIR__ . '/includes/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if (count($compareIds) > 0): ?>
<div id="compare-bar" class="fixed bottom-16 md:bottom-0 inset-x-0 bg-neutral-900 text-white z-50 px-6 py-3 flex items-center justify-between visible">
  <span class="text-xs font-bold uppercase tracking-widest"><?= count($compareIds) ?>/2 selected for compare</span>
  <button type="button" onclick="openCompareModal()" class="bg-[#FF3B00] text-white text-xs font-black px-6 py-2 uppercase tracking-widest">Open Compare</button>
</div>
<?php endif; ?>

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
