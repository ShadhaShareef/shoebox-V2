<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Home';
$activeNav = 'home';
$voltProduct = getProductById(100);
$accessories = getAccessories();
$trending = getProducts(null, 'Trending');
$heroProducts = array_slice(getProducts(null, null, null, 'popular'), 0, 5);
if (empty($heroProducts) && $voltProduct) {
    $heroProducts = [$voltProduct];
}
$comparePool = array_map('comparePayload', array_slice($trending, 0, 8));

$heroSlides = array_map(static function (array $product): array {
    $localImage = null;
    if (empty($product['image_url'])) {
        $nameLower = strtolower($product['name']);
        if (str_contains($nameLower, '550')) {
            $localImage = 'images/products/550ain.png';
        } elseif (str_contains($nameLower, 'samba')) {
            $localImage = 'images/products/samba.png';
        } elseif (str_contains($nameLower, 'jordan')) {
            $localImage = 'images/products/airjordan.png';
        } elseif (str_contains($nameLower, 'chuck')) {
            $localImage = 'images/products/chuck70.png';
        }
    }

    return [
        'id' => (int) $product['id'],
        'name' => $product['name'],
        'brand' => $product['brand'],
        'category' => $product['category'],
        'description' => $product['description'],
        'priceLabel' => formatPrice((float) $product['price']),
        'originalPriceLabel' => $product['original_price'] !== null ? formatPrice((float) $product['original_price']) : null,
        'ratingLabel' => number_format((float) $product['rating'], 1),
        'stockLabel' => !empty($product['stock_units']) ? ((int) $product['stock_units'] . ' units left') : null,
        'sizes' => $product['sizes'] ?? [],
        'shoeColor' => $product['shoe_color'] ?? '#FFFFFF',
        'accentColor' => $product['accent_color'] ?? '#FF3B00',
        'imageUrl' => $product['image_url'] ?: $localImage,
        'isExclusive' => !empty($product['is_exclusive']),
    ];
}, $heroProducts);

$heroProduct = $heroSlides[0] ?? [
    'id' => 100,
    'name' => 'Volt Elite V1 "Obsidian Crimson"',
    'brand' => 'Shoebox Labs',
    'category' => 'Exclusive Drop',
    'description' => 'Kerala-exclusive engineered performance silhouette.',
    'priceLabel' => formatPrice(18999),
    'originalPriceLabel' => null,
    'ratingLabel' => '5.0',
    'stockLabel' => null,
    'sizes' => [7, 8, 9, 10, 11],
    'shoeColor' => '#1A1A1A',
    'accentColor' => '#FF3B00',
    'isExclusive' => true,
];

require __DIR__ . '/includes/header.php';
?>

<!-- Scroll-Reactive Hero Stage -->
<div id="hero-scroll-wrapper" class="relative w-full" style="height: 350vh;">
  <section
    id="hero-sticky-stage"
    class="sticky top-0 left-0 w-full h-screen overflow-hidden hero-stage-bg text-white flex flex-col justify-between"
    data-hero-stage
    data-hero-slides="<?= e(json_encode($heroSlides)) ?>"
  >
    <!-- Background overlays & ambient glows -->
    <div class="noise-overlay"></div>
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <!-- Radial Orange Glow behind sneaker -->
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[70vw] h-[70vw] max-w-[800px] max-h-[800px] rounded-full bg-[#FF3B00]/15 blur-[120px] mix-blend-screen pointer-events-none transition-all duration-700" id="ambient-glow"></div>
      <!-- Soft Cyan Ambient light accent -->
      <div class="absolute bottom-10 left-10 w-96 h-96 rounded-full bg-cyan-500/10 blur-[100px] pointer-events-none"></div>
      <div class="absolute top-10 right-10 w-80 h-80 rounded-full bg-orange-500/5 blur-[90px] pointer-events-none"></div>
    </div>

    <!-- Main Visual Grid Stage -->
    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 h-full w-full max-w-[1400px] mx-auto px-6 sm:px-10 py-24 items-center gap-8">
      
      <!-- Left side: Typography Details (Col 1 to 5) -->
      <div class="lg:col-span-5 flex flex-col justify-center gap-6 select-text">
        <div class="space-y-2">
          <!-- Brand Badge -->
          <div class="inline-flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B00] animate-pulse"></span>
            <span id="hero-brand" class="text-[10px] font-bold uppercase tracking-[0.25em] text-neutral-400">
              <?= e($heroProduct['brand']) ?>
            </span>
          </div>
          <span id="hero-category" class="text-[#FF3B00] text-[10px] font-mono font-bold tracking-[0.3em] uppercase block">
            <?= e($heroProduct['isExclusive'] ? 'Kerala Exclusive Drop' : $heroProduct['brand']) ?>
          </span>
        </div>

        <div class="space-y-4">
          <h1 id="hero-title" class="font-display font-black text-5xl sm:text-6xl xl:text-7xl uppercase leading-[0.9] tracking-tight fade-transition">
            <?= e($heroProduct['name']) ?>
          </h1>
          <p id="hero-description" class="text-neutral-400 text-sm sm:text-base max-w-sm leading-relaxed fade-transition line-clamp-2">
            <?= e($heroProduct['description']) ?>
          </p>
        </div>

        <!-- Meta: Price + Rating -->
        <div class="flex items-center gap-6 py-2 border-y border-white/5 max-w-xs fade-transition">
          <div>
            <span class="text-[9px] uppercase tracking-[0.3em] text-neutral-500 block mb-1">PRICE</span>
            <span id="hero-price" class="text-2xl font-black text-white"><?= e($heroProduct['priceLabel']) ?></span>
            <?php if (!empty($heroProduct['originalPriceLabel'])): ?>
              <span id="hero-original-price" class="text-neutral-500 line-through text-xs ml-2"><?= e($heroProduct['originalPriceLabel']) ?></span>
            <?php else: ?>
              <span id="hero-original-price" class="hidden text-neutral-500 line-through text-xs ml-2"></span>
            <?php endif; ?>
          </div>
          <div class="w-px h-8 bg-white/10"></div>
          <div>
            <span class="text-[9px] uppercase tracking-[0.3em] text-neutral-500 block mb-1">RATING</span>
            <span id="hero-rating" class="text-amber-400 text-sm font-bold flex items-center gap-1">
              ★ <span id="hero-rating-value"><?= e($heroProduct['ratingLabel']) ?></span>
            </span>
          </div>
          <span id="hero-stock" class="text-[9px] font-mono text-[#FF3B00] border border-[#FF3B00] px-2 py-0.5 ml-auto <?= empty($heroProduct['stockLabel']) ? 'hidden' : '' ?>">
            <?= e($heroProduct['stockLabel'] ?? '') ?>
          </span>
        </div>

        <!-- Actions Form -->
        <form
          action="<?= url('api/cart.php') ?>"
          method="POST"
          class="flex flex-wrap gap-3"
          data-hero-form
        >
          <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= (int) $heroProduct['id'] ?>">
          <input type="hidden" name="size" id="size-hero">
          
          <button type="submit" class="bg-[#FF3B00] hover:bg-orange-600 text-white text-xs font-black px-8 py-4 uppercase tracking-widest transition-colors shadow-lg shadow-[#FF3B00]/20">
            Reserve Pair
          </button>
          <a href="<?= url('product.php?id=' . (int) $heroProduct['id']) ?>" id="hero-link" class="border border-neutral-800 bg-neutral-900/50 backdrop-blur-sm text-neutral-300 text-xs font-black px-8 py-4 uppercase tracking-widest hover:border-white hover:text-white transition-all flex items-center">
            View Product
          </a>
        </form>
      </div>

      <!-- Center: Sneaker Stage (Col 6 to 10) -->
      <div class="lg:col-span-5 flex items-center justify-center relative min-h-[320px] lg:min-h-0">
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
          <div class="w-72 h-72 rounded-full bg-current opacity-20 blur-3xl" id="product-accent-glow" style="color: <?= e($heroProduct['accentColor']) ?>;"></div>
        </div>
        <div class="w-full max-w-md lg:max-w-lg relative z-10 flex items-center justify-center">
          <div id="hero-sneaker-container" class="w-full h-80 sm:h-96 flex items-center justify-center <?= empty($heroProduct['imageUrl']) ? 'sneaker-graphic' : '' ?>" data-color="<?= e($heroProduct['shoeColor']) ?>" data-accent="<?= e($heroProduct['accentColor']) ?>">
            <img 
              id="hero-image"
              src="<?= !empty($heroProduct['imageUrl']) ? asset($heroProduct['imageUrl']) : '' ?>" 
              alt="<?= e($heroProduct['name']) ?>" 
              class="max-h-[85%] max-w-[85%] object-contain sneaker-stage-image animate-none <?= empty($heroProduct['imageUrl']) ? 'hidden' : '' ?>"
              style="--sneaker-rotation: -8deg; --sneaker-translation: 0px;"
            >
          </div>
        </div>
      </div>

      <!-- Right side: Premium Minimal Selectors & Index Indicators (Col 11 to 12) -->
      <div class="lg:col-span-2 flex flex-row lg:flex-col items-center justify-between lg:justify-center gap-8 h-full lg:border-l border-white/5 lg:pl-8">
        
        <!-- Minimal Index Counter -->
        <div class="text-left lg:text-center font-mono">
          <span id="hero-index-current" class="text-2xl font-black text-white">01</span>
          <span class="text-neutral-600 mx-1">/</span>
          <span id="hero-index-total" class="text-xs font-bold text-neutral-500"><?= sprintf('%02d', count($heroSlides)) ?></span>
        </div>

        <!-- Scroll Indicator / Progress line -->
        <div class="hidden lg:block w-px h-24 bg-neutral-800 relative overflow-hidden">
          <div id="hero-scroll-progress-bar" class="absolute top-0 left-0 w-full bg-[#FF3B00] transition-all duration-100" style="height: 0%;"></div>
        </div>

        <!-- Thumbnail selector list -->
        <div class="flex flex-row lg:flex-col gap-3" id="hero-thumbnails-container">
          <?php foreach ($heroSlides as $index => $product): ?>
            <button
              type="button"
              data-thumbnail-index="<?= $index ?>"
              class="w-12 h-12 rounded-xl border-2 bg-neutral-900/50 backdrop-blur-sm border-white/10 hover:border-white/30 transition-all flex items-center justify-center p-1 overflow-hidden focus:outline-none"
              title="<?= e($product['name']) ?>"
            >
              <?php if (!empty($product['imageUrl'])): ?>
                <img src="<?= asset($product['imageUrl']) ?>" alt="" class="w-full h-full object-contain pointer-events-none transform -rotate-12">
              <?php else: ?>
                <div class="w-4 h-4 rounded-full" style="background-color: <?= e($product['shoeColor']) ?>"></div>
              <?php endif; ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

    <!-- Small Footer Accent within Stage -->
    <div class="absolute bottom-4 left-6 sm:left-10 z-10 pointer-events-none hidden sm:flex items-center gap-3">
      <span class="text-[9px] font-bold uppercase tracking-[0.35em] text-neutral-500">Shoebox Drop Stage</span>
      <div class="w-8 h-px bg-neutral-800"></div>
      <span class="text-[9px] font-mono text-neutral-600">Scroll to explore</span>
    </div>
  </section>
</div>

<!-- Trending strip -->
<section class="px-6 sm:px-10 py-12">
  <div class="flex items-center justify-between mb-8">
    <h2 class="font-display font-black text-2xl uppercase tracking-widest">Trending Now</h2>
    <a href="<?= url('shop.php?category=Trending') ?>" class="text-xs font-bold uppercase tracking-widest text-[#FF3B00] hover:underline">View All -></a>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <?php foreach (array_slice($trending, 0, 4) as $product): ?>
      <?php include __DIR__ . '/includes/product-card.php'; ?>
    <?php endforeach; ?>
  </div>
</section>

<!-- Accessories -->
<section class="px-6 sm:px-10 py-12 bg-white border-y border-[#E8E5DF]">
  <h2 class="font-display font-black text-2xl uppercase tracking-widest mb-2">Complete the Look</h2>
  <p class="text-sm text-neutral-500 mb-8">Curated Shoebox Gear to pair with your drop.</p>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <?php foreach ($accessories as $acc): ?>
    <div class="bg-[#FAF9F5] border border-[#E8E5DF] p-4 flex flex-col justify-between group hover:border-black transition-all">
      <div>
        <div class="w-full h-36 bg-white flex items-center justify-center mb-4">
          <?php if (!empty($acc['image_url'])): ?>
            <img src="<?= asset($acc['image_url']) ?>" alt="<?= e($acc['name']) ?>" class="max-h-[85%] max-w-[85%] object-contain transition-transform duration-300 group-hover:scale-105">
          <?php else: ?>
            <div class="w-20 h-20 rounded" style="background:<?= e($acc['shoe_color']) ?>"></div>
          <?php endif; ?>
        </div>
        <span class="text-[8px] font-mono text-zinc-400 tracking-widest uppercase">SHOEBOX GEAR</span>
        <h4 class="text-sm font-black uppercase mt-0.5"><?= e($acc['name']) ?></h4>
        <p class="text-[11px] text-neutral-400 mt-1 line-clamp-2"><?= e($acc['description']) ?></p>
      </div>
      <div class="flex justify-between items-center mt-4 pt-3 border-t border-dashed border-[#E8E5DF]">
        <span class="text-sm font-black"><?= formatPrice($acc['price']) ?></span>
        <form action="<?= url('api/cart.php') ?>" method="POST">
          <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?= (int)$acc['id'] ?>">
          <button type="submit" class="bg-neutral-900 hover:bg-[#FF3B00] text-white text-[9px] font-black px-3 py-1.5 uppercase tracking-widest">+ ADD</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Authenticity story -->
<section class="px-6 sm:px-10 py-16 text-center max-w-2xl mx-auto">
  <span class="text-[10px] font-mono text-[#FF3B00] tracking-[0.3em] uppercase">Authenticity Guarantee</span>
  <h2 class="font-display font-black text-3xl uppercase mt-3 mb-4">Every Pair Verified</h2>
  <p class="text-sm text-neutral-600 leading-relaxed">Multi-point authentication at our Kochi Lulu Mall hub. Stitching, leather grain, SKU codes, and tamper-proof security tags on every delivery across Kerala.</p>
  <a href="<?= url('about.php') ?>" class="inline-block mt-6 text-xs font-bold uppercase tracking-widest text-[#FF3B00] border-b-2 border-[#FF3B00] pb-1">Read FAQ -></a>
</section>

<!-- Size modal -->
<div id="size-modal" class="fixed inset-0 z-[80] items-center justify-center bg-black/60">
  <div class="bg-white p-6 max-w-sm w-full mx-4 shadow-2xl">
    <h3 class="font-display font-black uppercase tracking-widest mb-4">Select UK Size</h3>
    <div id="size-grid" class="flex flex-wrap gap-2 mb-6"></div>
    <div class="flex gap-3">
      <button type="button" onclick="closeSizeModal()" class="flex-1 border border-neutral-300 py-2 text-xs font-bold uppercase">Cancel</button>
      <button type="button" id="size-confirm" class="flex-1 bg-neutral-900 text-white py-2 text-xs font-bold uppercase">Add to Bag</button>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
