<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Brands';
$activeNav = 'brands';
$brands = getBrands();

$brandMeta = [
    'New Balance' => ['desc' => 'Boston heritage runners & terrace classics', 'color' => '#EAE9E4', 'logo' => 'images/brands/new-balance.png'],
    'Adidas Originals' => ['desc' => 'Samba, Gazelle & football terrace icons', 'color' => '#FFFFFF', 'logo' => 'images/brands/adidas.png'],
    'Nike' => ['desc' => 'Jordan, Air Force & court legends', 'color' => '#8E9196', 'logo' => 'images/brands/nike.png'],
    'Converse' => ['desc' => 'Canvas culture since the Chuck 70', 'color' => '#FFFDF2', 'logo' => 'images/brands/converse.png'],
    'Puma' => ['desc' => 'Palermo terrace & speed silhouettes', 'color' => '#3B82F6', 'logo' => 'images/brands/puma.png'],
    'Asics' => ['desc' => 'GEL-KAYANO tech runners reimagined', 'color' => '#A1A1AA', 'logo' => 'images/brands/asics.png'],
];

require __DIR__ . '/includes/header.php';
?>

<section class="px-6 sm:px-10 py-12">
  <h1 class="font-display font-black text-3xl uppercase tracking-widest mb-2">Brand Vault</h1>
  <p class="text-sm text-neutral-500 mb-10">Select a house to explore authenticated pairs in our Kerala catalogue.</p>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($brands as $brand):
      $meta = $brandMeta[$brand] ?? ['desc' => 'Premium authenticated sneakers', 'color' => '#E8E5DF', 'logo' => ''];
      $count = count(getProducts($brand));
    ?>
    <a href="<?= url('shop.php?brand=' . urlencode($brand)) ?>" class="group bg-white border border-[#E8E5DF] hover:border-neutral-900 p-8 transition-all block">
      <div class="w-full h-32 flex items-center justify-center mb-6 p-4" style="background:<?= e($meta['color']) ?>20">
        <?php if (!empty($meta['logo'])): ?>
          <img src="<?= asset($meta['logo']) ?>" alt="<?= e($brand) ?>" class="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-105">
        <?php else: ?>
          <div class="w-24 h-16 rounded-lg flex items-center justify-center text-xs font-black text-white" style="background:<?= e($meta['color']) ?>; box-shadow: inset -4px -2px 0 #FF3B00">
            <?= substr(e($brand), 0, 2) ?>
          </div>
        <?php endif; ?>
      </div>
      <h2 class="font-display font-black text-xl uppercase group-hover:text-[#FF3B00] transition-colors"><?= e($brand) ?></h2>
      <p class="text-xs text-neutral-500 mt-2"><?= e($meta['desc']) ?></p>
      <span class="inline-block mt-4 text-[10px] font-mono text-[#FF3B00] uppercase tracking-widest"><?= $count ?> pairs →</span>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
