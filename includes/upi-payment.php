<?php
/** @var float $total */
/** @var array $delivery */
$csrf = csrfToken();
$prefillName = e($delivery['full_name'] ?? '');
$prefillEmail = e($delivery['email'] ?? '');
$prefillPhone = e($delivery['phone'] ?? '');
?>
<div id="upi-payment-panel" class="space-y-4" data-total="<?= $total ?>" data-csrf="<?= e($csrf) ?>"
     data-prefill-name="<?= $prefillName ?>" data-prefill-email="<?= $prefillEmail ?>" data-prefill-phone="<?= $prefillPhone ?>">

  <div class="flex items-center justify-between border-b border-[#E8E5DF] pb-2">
    <div class="flex items-center gap-1.5">
      <span class="relative flex h-2 w-2">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
      </span>
      <span class="text-[9px] font-mono font-extrabold tracking-widest uppercase">Razorpay UPI Channel</span>
    </div>
    <span id="razorpay-mode-badge" class="text-[8px] font-extrabold px-1.5 py-0.5 uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">Connecting…</span>
  </div>

  <div id="upi-loading" class="py-6 text-center">
    <div class="w-8 h-8 border-2 border-[#FF3B00] border-t-transparent rounded-full animate-spin mx-auto"></div>
    <p class="font-mono text-[9px] text-neutral-400 uppercase tracking-widest mt-3">Provisioning Razorpay gateway…</p>
  </div>

  <div id="upi-error" class="hidden py-4 text-center text-red-600 text-xs font-bold"></div>

  <div id="upi-ready" class="hidden space-y-4 text-center">
    <div>
      <span class="text-[9px] font-mono text-neutral-400 uppercase tracking-widest block">Total Amount</span>
      <span class="text-2xl font-black font-display"><?= formatPrice($total) ?></span>
      <p class="text-[9px] font-mono text-neutral-400 mt-1 truncate">RPAY REF: <span id="razorpay-order-ref" class="text-neutral-900 font-bold"></span></p>
    </div>

    <div class="relative w-44 h-44 mx-auto p-1.5 bg-white border border-neutral-300 shadow-sm">
      <img id="upi-qr-image" src="" alt="UPI QR Code" class="w-full h-full object-contain" referrerpolicy="no-referrer">
      <div class="absolute top-1 left-1 w-3 h-3 bg-[#FF3B00]"></div>
      <div class="absolute top-1 right-1 w-3 h-3 bg-[#FF3B00]"></div>
      <div class="absolute bottom-1 left-1 w-3 h-3 bg-[#FF3B00]"></div>
      <div class="absolute bottom-1 right-1 w-3 h-3 bg-neutral-900"></div>
    </div>

    <p class="text-[10px] font-mono text-neutral-500 max-w-xs mx-auto">Scan with GPay, PhonePe, Paytm, or BHIM. Or use Razorpay Checkout below.</p>

    <button type="button" id="razorpay-checkout-btn" class="w-full bg-neutral-900 hover:bg-[#FF3B00] text-white font-black py-3 text-[10px] tracking-widest uppercase transition-colors">
      Open Razorpay Checkout (UPI / Card)
    </button>

    <button type="button" id="razorpay-mock-complete" class="hidden w-full border-2 border-dashed border-emerald-400 text-emerald-700 font-black py-2.5 text-[9px] tracking-widest uppercase hover:bg-emerald-50">
      ✓ Confirm Sandbox Payment
    </button>

    <div class="bg-[#FAF9F5] border border-[#E8E5DF] p-2.5 text-[10px] text-neutral-600 flex items-center gap-2">
      <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
      <span class="font-semibold text-left">256-bit SSL. Payment verified server-side before order creation.</span>
    </div>
  </div>
</div>
