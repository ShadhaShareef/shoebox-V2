<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'FAQ & Contact';
$activeNav = 'about';

$faqSearch = trim($_GET['q'] ?? '');
$faqCategory = $_GET['category'] ?? 'All';
$faqs = getFaqItems($faqCategory, $faqSearch ?: null);
$faqCategories = array_merge(['All'], getFaqCategories());

require __DIR__ . '/includes/header.php';
?>

<section class="px-6 sm:px-10 py-12">
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
    <!-- FAQ -->
    <div>
      <h1 class="font-display font-black text-3xl uppercase tracking-widest mb-6">FAQ</h1>

      <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="q" value="<?= e($faqSearch) ?>" placeholder="Search questions..." class="flex-1 border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-black">
        <button type="submit" class="bg-neutral-900 text-white px-4 text-xs font-bold uppercase">Search</button>
      </form>

      <div class="flex flex-wrap gap-2 mb-6">
        <?php foreach ($faqCategories as $cat): ?>
        <a href="<?= url('about.php?category=' . urlencode($cat)) ?>" class="text-[10px] font-bold uppercase tracking-wider px-3 py-1 border <?= $faqCategory === $cat ? 'bg-neutral-900 text-white border-neutral-900' : 'border-[#E8E5DF] text-neutral-500 hover:border-black' ?>">
          <?= e($cat) ?>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="space-y-2">
        <?php foreach ($faqs as $faq): ?>
        <div class="faq-item bg-white border border-[#E8E5DF]">
          <button type="button" class="faq-toggle w-full text-left p-4 flex items-center justify-between gap-4" aria-expanded="false">
            <div>
              <span class="text-[9px] font-mono text-[#FF3B00] uppercase"><?= e($faq['category']) ?></span>
              <p class="text-sm font-bold mt-1"><?= e($faq['question']) ?></p>
            </div>
            <svg class="faq-chevron w-5 h-5 flex-shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div class="faq-answer hidden px-4 pb-4">
            <p class="text-sm text-neutral-600 leading-relaxed"><?= e($faq['answer']) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Contact -->
    <div>
      <h2 class="font-display font-black text-2xl uppercase tracking-widest mb-6">Contact Concierge</h2>

      <div class="bg-neutral-900 text-white p-6 mb-6 space-y-3 text-sm">
        <p class="flex items-center gap-3">
          <svg class="w-5 h-5 text-[#FF3B00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
          WhatsApp: +91 90796 44290
        </p>
        <p class="flex items-center gap-3">
          <svg class="w-5 h-5 text-[#FF3B00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          concierge@shoeboxkerala.com
        </p>
        <p class="flex items-center gap-3">
          <svg class="w-5 h-5 text-[#FF3B00]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
          Lulu Mall, Kochi
        </p>
      </div>

      <form action="<?= url('api/contact.php') ?>" method="POST" class="bg-white border border-[#E8E5DF] p-6 space-y-4">
        <?= csrfField() ?>
        <div class="hidden" aria-hidden="true">
          <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>
        <input type="text" name="full_name" required placeholder="Full Name" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-black">
        <input type="email" name="email" required placeholder="Email" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-black">
        <input type="tel" name="phone" placeholder="Phone" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-black">
        <select name="subject" class="w-full border border-[#E8E5DF] px-3 py-2 text-sm">
          <option>Product Authentication</option>
          <option>Order & Delivery</option>
          <option>Size Exchange</option>
          <option>Store Pickup</option>
          <option>General Inquiry</option>
        </select>
        <textarea name="message" required rows="4" placeholder="Your message..." class="w-full border border-[#E8E5DF] px-3 py-2 text-sm focus:outline-none focus:border-black"></textarea>
        <button type="submit" class="w-full bg-[#FF3B00] hover:bg-orange-600 text-white text-xs font-black py-4 uppercase tracking-widest">Send Inquiry</button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
