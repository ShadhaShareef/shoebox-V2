<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Stores';
$activeNav = 'stores';
$stores = getStores();
$selectedId = $_GET['id'] ?? null;

require __DIR__ . '/includes/header.php';
?>

<section class="px-6 sm:px-10 py-12">
  <h1 class="font-display font-black text-3xl uppercase tracking-widest mb-2">Kerala Flagships</h1>
  <p class="text-sm text-neutral-500 mb-10 max-w-2xl">Experience launch areas, limited-drops vaults, and premium accessories across our Kerala showrooms.</p>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php foreach ($stores as $store): ?>
    <div class="bg-white border border-[#E8E5DF] hover:border-black transition-all overflow-hidden <?= $selectedId === $store['id'] ? 'ring-2 ring-[#FF3B00]' : '' ?>">
      <div class="bg-neutral-900 text-white p-6">
        <span class="text-[9px] font-mono text-[#FF3B00] uppercase tracking-widest">Flagship Hub</span>
        <h2 class="font-display font-black text-lg uppercase mt-1"><?= e($store['name']) ?></h2>
      </div>
      <div class="p-6 space-y-3 text-sm">
        <p class="flex items-start gap-2 text-neutral-600">
          <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
          <?= e($store['location']) ?>
        </p>
        <p class="text-[10px] font-mono text-neutral-400"><?= e($store['coordinates']) ?></p>
        <p class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> <?= e($store['hours']) ?></p>
        <p class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> <?= e($store['phone']) ?></p>
        <ul class="pt-3 border-t border-dashed border-[#E8E5DF] space-y-1">
          <?php foreach ($store['features'] as $f): ?>
          <li class="text-[11px] text-neutral-500 flex items-center gap-1">
            <span class="text-[#FF3B00]">✦</span> <?= e($f) ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= e($store['directions_url']) ?>" target="_blank" class="inline-block mt-4 bg-neutral-900 text-white text-[10px] font-black px-4 py-2 uppercase tracking-widest hover:bg-[#FF3B00] transition-colors">Get Directions</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
