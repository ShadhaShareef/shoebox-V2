<?php
/** @var string $pageTitle */
/** @var string $activeNav */
$pageTitle = $pageTitle ?? APP_NAME;
$activeNav = $activeNav ?? '';
$user = currentUser();
$cartCount = cartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
  <title><?= e($pageTitle) ?> — Kerala Premium Sneakers</title>
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=<?= time() ?>">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Plus Jakarta Sans', 'sans-serif'],
            display: ['Outfit', 'sans-serif'],
            mono: ['JetBrains Mono', 'monospace'],
          }
        }
      }
    }
  </script>
</head>
<body class="bg-[#F5F4F0] text-[#0A0A0A] font-sans">

<div class="bg-[#FF3B00] text-white text-[10px] sm:text-xs font-semibold py-1.5 px-4 text-center tracking-widest uppercase">
  🏷️ MONSOON DROP: Enjoy Free Express Hand-shipping Across Kerala on orders above ₹15,000!
</div>

<div class="max-w-[1440px] mx-auto bg-[#F5F4F0] min-h-screen flex flex-col shadow-xl border-x border-[#E8E5DF]">

<nav id="main-nav" class="sticky top-0 z-50 px-4 sm:px-8 flex items-center justify-between border-b py-5 bg-neutral-900 border-neutral-800 transition-all duration-300">
  <div class="flex items-center gap-6 lg:gap-12">
    <a href="<?= url('index.php') ?>" class="text-2xl sm:text-3xl font-black tracking-widest hover:text-[#FF3B00] transition-colors font-display text-white">SHOEBOX</a>
    <div class="hidden md:flex gap-6 lg:gap-8 text-xs font-semibold tracking-widest uppercase">
      <a href="<?= url('index.php') ?>" class="nav-link <?= $activeNav === 'home' ? 'nav-active' : '' ?>">Shop</a>
      <a href="<?= url('brands.php') ?>" class="nav-link <?= $activeNav === 'brands' ? 'nav-active' : '' ?>">Brands</a>
      <a href="<?= url('shop.php') ?>" class="nav-link <?= $activeNav === 'shop' ? 'nav-active' : '' ?>">Collections</a>
      <a href="<?= url('sale.php') ?>" class="nav-link <?= $activeNav === 'sale' ? 'nav-active' : '' ?>">Sale</a>
      <a href="<?= url('stores.php') ?>" class="nav-link <?= $activeNav === 'stores' ? 'nav-active' : '' ?>">Stores</a>
      <a href="<?= url('about.php') ?>" class="nav-link <?= $activeNav === 'about' ? 'nav-active' : '' ?>">FAQ</a>
    </div>
  </div>
  <div class="flex items-center gap-3 sm:gap-5">
    <a href="<?= url('shop.php') ?>" class="hidden sm:flex text-neutral-400 hover:text-white transition-colors" title="Search">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </a>
    <a href="<?= $user ? url('auth.php') : url('login.php') ?>" class="text-neutral-400 hover:text-white transition-colors" title="Account">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    </a>
    <button type="button" onclick="openCart()" class="relative text-neutral-400 hover:text-white transition-colors" title="Cart">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
      <?php if ($cartCount > 0): ?>
      <span class="absolute -top-2 -right-2 bg-[#FF3B00] text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center"><?= $cartCount ?></span>
      <?php endif; ?>
    </button>
  </div>
</nav>

<main class="flex-1">

<!-- Toast container -->
<div id="toast-container" class="fixed top-20 right-4 z-[100] flex flex-col gap-2 max-w-sm"></div>

<?php foreach (getFlashes() as $flash): ?>
<script>document.addEventListener('DOMContentLoaded', () => showToast('<?= e($flash['title']) ?>', '<?= e($flash['description']) ?>', '<?= e($flash['type']) ?>'));</script>
<?php endforeach; ?>
