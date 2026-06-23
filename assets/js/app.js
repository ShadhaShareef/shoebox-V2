// Shoebox PHP — client-side helpers

document.addEventListener('DOMContentLoaded', () => {
  // Sticky nav scroll effect
  const nav = document.getElementById('main-nav');
  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });
  }

  // Sneaker color placeholders
  document.querySelectorAll('.sneaker-graphic, .sneaker-mini').forEach(el => {
    const color = el.dataset.color || '#EAE9E4';
    const accent = el.dataset.accent || '#FF3B00';
    el.style.setProperty('--shoe-color', color);
    el.style.setProperty('--accent-color', accent);
  });

  // FAQ accordion
  document.querySelectorAll('.faq-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const item = btn.closest('.faq-item');
      const answer = item.querySelector('.faq-answer');
      const wasOpen = item.classList.contains('open');

      document.querySelectorAll('.faq-item').forEach(i => {
        i.classList.remove('open');
        const toggle = i.querySelector('.faq-toggle');
        const panel = i.querySelector('.faq-answer');
        toggle?.setAttribute('aria-expanded', 'false');
        panel?.classList.add('hidden');
      });

      if (!wasOpen) item.classList.add('open');
      if (answer) answer.classList.toggle('hidden', wasOpen);
      btn.setAttribute('aria-expanded', String(!wasOpen));
    });
  });

  initHeroCarousels();
  initRazorpayUpiPanel();
  initCompareUI();
});

let razorpayOrderData = null;

function apiUrl(path) {
  const base = (window.SHOEBOX && window.SHOEBOX.baseUrl) || '';
  return base.replace(/\/$/, '') + '/' + path.replace(/^\//, '');
}

function getCsrf() {
  return (window.SHOEBOX && window.SHOEBOX.csrf)
    || document.querySelector('meta[name="csrf-token"]')?.content
    || '';
}

async function initRazorpayUpiPanel() {
  const panel = document.getElementById('upi-payment-panel');
  if (!panel) return;

  const loading = document.getElementById('upi-loading');
  const ready = document.getElementById('upi-ready');
  const errorEl = document.getElementById('upi-error');
  const badge = document.getElementById('razorpay-mode-badge');
  const mockBtn = document.getElementById('razorpay-mock-complete');
  const checkoutBtn = document.getElementById('razorpay-checkout-btn');

  try {
    const res = await fetch(apiUrl('api/razorpay-order.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ csrf_token: panel.dataset.csrf || getCsrf() }),
    });

    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Gateway unavailable');

    razorpayOrderData = data;
    loading.classList.add('hidden');
    ready.classList.remove('hidden');

    document.getElementById('razorpay-order-ref').textContent = data.id;
    document.getElementById('upi-qr-image').src = data.qrUrl;
    checkoutBtn.disabled = !!data.isMock;
    checkoutBtn.textContent = data.isMock ? 'Live Checkout Disabled' : 'Open Razorpay Checkout (UPI / Card)';
    checkoutBtn.title = data.isMock ? 'Sandbox mode uses the confirmation button below instead of the Razorpay widget.' : '';

    if (data.isMock) {
      badge.textContent = '⚡ Sandbox';
      badge.className = 'text-[8px] font-extrabold px-1.5 py-0.5 uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200';
      mockBtn.classList.remove('hidden');
    } else {
      badge.textContent = '● Live Secured';
      badge.className = 'text-[8px] font-extrabold px-1.5 py-0.5 uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200';
    }

    mockBtn?.addEventListener('click', () => completeRazorpayPayment({
      razorpay_order_id: data.id,
      razorpay_payment_id: 'pay_mock_' + Math.random().toString(36).slice(2, 11),
      razorpay_signature: 'mock_sig_' + Math.random().toString(36).slice(2, 14),
    }));

    checkoutBtn?.addEventListener('click', () => {
      if (data.isMock) {
        showToast('Sandbox Mode', 'Use the confirm button to simulate payment while test keys are configured.', 'info');
        return;
      }

      openRazorpayCheckout(data, panel);
    });
  } catch (err) {
    loading.classList.add('hidden');
    errorEl.textContent = err.message || 'Could not connect to Razorpay';
    errorEl.classList.remove('hidden');
    badge.textContent = 'Offline';
    badge.className = 'text-[8px] font-extrabold px-1.5 py-0.5 uppercase bg-red-100 text-red-800';
  }
}

function openRazorpayCheckout(data, panel) {
  if (data.isMock) {
    showToast('Sandbox Mode', 'Use the confirm button to simulate payment while test keys are configured.', 'info');
    return;
  }

  if (typeof Razorpay === 'undefined') {
    showToast('Razorpay Unavailable', 'Checkout script failed to load.', 'warning');
    return;
  }

  const amountPaise = Math.round(parseFloat(panel.dataset.total) * 100);

  const options = {
    key: data.keyId,
    amount: data.amountPaise || amountPaise,
    currency: data.currency || 'INR',
    name: 'Shoebox Kerala',
    description: 'Premium Sneaker Order',
    order_id: data.isMock ? undefined : data.id,
    prefill: {
      name: panel.dataset.prefillName || '',
      email: panel.dataset.prefillEmail || '',
      contact: panel.dataset.prefillPhone || '',
    },
    theme: { color: '#FF3B00' },
    method: { upi: true, card: true, netbanking: false, wallet: true },
    handler: function (response) {
      completeRazorpayPayment(response);
    },
    modal: {
      ondismiss: function () {
        showToast('Payment Cancelled', 'You can retry or choose Cash on Delivery.', 'info');
      },
    },
  };

  const rzp = new Razorpay(options);
  rzp.on('payment.failed', function (resp) {
    showToast('Payment Failed', resp.error?.description || 'Please try again.', 'warning');
  });
  rzp.open();
}

async function completeRazorpayPayment(response) {
  try {
    const res = await fetch(apiUrl('api/razorpay-verify.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        csrf_token: getCsrf(),
        razorpay_order_id: response.razorpay_order_id,
        razorpay_payment_id: response.razorpay_payment_id,
        razorpay_signature: response.razorpay_signature,
      }),
    });

    const data = await res.json();
    if (!res.ok || !data.success) throw new Error(data.error || 'Verification failed');

    showToast('Order Placed', 'Tracking: ' + data.orderId, 'success');
    window.location.reload();
  } catch (err) {
    showToast('Checkout Error', err.message || 'Payment could not be verified.', 'warning');
  }
}

function showToast(title, description, type = 'success') {
  const container = document.getElementById('toast-container');
  if (!container) return;
  const colors = { success: 'border-green-500', info: 'border-blue-500', warning: 'border-amber-500' };
  const toast = document.createElement('div');
  toast.className = `toast bg-white border-l-4 ${colors[type] || colors.success} shadow-lg p-4 max-w-sm`;
  toast.innerHTML = `<p class="font-bold text-sm">${title}</p>${description ? `<p class="text-xs text-neutral-500 mt-1">${description}</p>` : ''}`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 4000);
}

function openCart() {
  document.getElementById('cart-drawer')?.classList.add('open');
  document.getElementById('cart-overlay')?.classList.add('open');
}

function closeCart() {
  document.getElementById('cart-drawer')?.classList.remove('open');
  document.getElementById('cart-overlay')?.classList.remove('open');
}

function initCompareUI() {
  const buttons = document.querySelectorAll('[data-compare-trigger]');
  if (!buttons.length) return;

  buttons.forEach(btn => {
    btn.addEventListener('click', async () => {
      const payload = readComparePayload(btn);
      if (!payload) return;

      try {
        const res = await fetch(apiUrl('api/compare.php'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: new URLSearchParams({
            csrf_token: getCsrf(),
            ajax: '1',
            mode: 'primary',
            product_id: String(payload.id || ''),
          }),
        });

        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Could not open compare modal');

        window.SHOEBOX_COMPARE = window.SHOEBOX_COMPARE || {};
        window.SHOEBOX_COMPARE.active = data.compareIds || [payload.id];
        openCompareModal(payload);
      } catch (err) {
        showToast('Compare Error', err.message || 'Could not load compare view.', 'warning');
      }
    });
  });
}

function readComparePayload(btn) {
  try {
    return JSON.parse(btn.dataset.compareProduct || 'null');
  } catch (err) {
    return null;
  }
}

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function comparePool() {
  return (window.SHOEBOX_COMPARE && Array.isArray(window.SHOEBOX_COMPARE.pool))
    ? window.SHOEBOX_COMPARE.pool
    : [];
}

function compareActiveIds() {
  return (window.SHOEBOX_COMPARE && Array.isArray(window.SHOEBOX_COMPARE.active))
    ? window.SHOEBOX_COMPARE.active
    : [];
}

function getCompareModalEls() {
  return {
    overlay: document.getElementById('compare-overlay'),
    modal: document.getElementById('compare-modal'),
    primary: document.getElementById('compare-primary'),
    activeList: document.getElementById('compare-active-list'),
    pool: document.getElementById('compare-pool'),
    count: document.getElementById('compare-count'),
  };
}

function openCompareModal(product) {
  const els = getCompareModalEls();
  if (!els.modal || !els.overlay) return;

  window.SHOEBOX_COMPARE = window.SHOEBOX_COMPARE || {};
  const active = compareActiveIds();
  const selected = product || comparePool().find(item => item.id === active[0]) || null;
  const secondary = comparePool().find(item => item.id === active[1]) || null;

  renderCompareModal(selected, secondary, active);
  els.modal.classList.remove('hidden');
  els.modal.classList.add('flex');
  els.overlay.classList.remove('hidden');
}

function closeCompareModal() {
  const els = getCompareModalEls();
  els.modal?.classList.add('hidden');
  els.modal?.classList.remove('flex');
  els.overlay?.classList.add('hidden');
}

function renderCompareModal(primary, secondary, activeIds) {
  const els = getCompareModalEls();
  if (!els.primary || !els.activeList || !els.pool || !els.count) return;

  if (!primary) {
    els.primary.innerHTML = '<p class="text-sm text-neutral-500">Pick a sneaker to start comparing.</p>';
    els.activeList.innerHTML = '';
    els.pool.innerHTML = '';
    els.count.textContent = '0 / 2';
    return;
  }

  els.count.textContent = `${activeIds.length || 1} / 2 selected`;
  els.primary.innerHTML = `
    <div class="bg-[#FAF9F5] border border-[#E8E5DF] flex items-center justify-center min-h-[260px] ${primary.imageUrl ? '' : 'sneaker-graphic'}" data-color="${escapeHtml(primary.shoeColor || '#FFFFFF')}" data-accent="${escapeHtml(primary.accentColor || '#FF3B00')}">
      ${primary.imageUrl ? `<img src="${apiUrl('assets/' + primary.imageUrl)}" alt="${escapeHtml(primary.name || '')}" class="max-h-[240px] object-contain p-4">` : ''}
    </div>
    <div>
      <p class="text-[9px] font-mono text-neutral-400 uppercase tracking-widest">${escapeHtml(primary.brand || '')}</p>
      <h3 class="font-display font-black text-2xl uppercase mt-1">${escapeHtml(primary.name || '')}</h3>
      <div class="flex items-center gap-3 mt-3">
        <span class="text-xl font-black">${escapeHtml(primary.priceLabel || '')}</span>
        ${primary.originalPriceLabel ? `<span class="text-neutral-400 line-through">${escapeHtml(primary.originalPriceLabel)}</span>` : ''}
        <span class="text-amber-600 text-sm ml-auto">★ ${escapeHtml(primary.ratingLabel || '0.0')}</span>
      </div>
      <p class="text-sm text-neutral-600 leading-relaxed mt-4">${escapeHtml(primary.description || '')}</p>
      <div class="mt-4 flex flex-wrap gap-2">
        ${(primary.sizes || []).map(size => `<span class="border border-[#E8E5DF] px-3 py-1 text-[10px] font-bold uppercase">UK ${escapeHtml(size)}</span>`).join('')}
      </div>
    </div>
  `;

  const currentItems = [];
  const pool = comparePool();
  const ids = activeIds.slice(0, 2);
  ids.forEach(id => {
    const item = pool.find(entry => entry.id === id);
    if (item) currentItems.push(item);
  });
  if (currentItems.length === 0) currentItems.push(primary);

  els.activeList.innerHTML = currentItems.map(item => `
    <div class="bg-white border border-[#E8E5DF] p-4">
      <p class="text-[9px] font-mono text-neutral-400 uppercase tracking-widest">${escapeHtml(item.brand || '')}</p>
      <h4 class="text-sm font-black uppercase mt-1">${escapeHtml(item.name || '')}</h4>
      <p class="text-sm font-black mt-2">${escapeHtml(item.priceLabel || '')}</p>
      <p class="text-[10px] text-neutral-500 mt-2">${escapeHtml(item.description || '')}</p>
    </div>
  `).join('');

  const candidates = pool.filter(item => item.id !== primary.id);
  if (!candidates.length) {
    els.pool.innerHTML = '<p class="text-sm text-neutral-500">No more products on this page to add yet.</p>';
    return;
  }

  els.pool.innerHTML = candidates.map(item => `
    <button type="button" class="w-full text-left bg-white border border-[#E8E5DF] hover:border-black transition-colors p-3 flex gap-3 items-center" data-compare-add="${escapeHtml(item.id)}">
      <div class="w-12 h-12 bg-[#FAF9F5] flex items-center justify-center flex-shrink-0 ${item.imageUrl ? '' : 'sneaker-mini'}" data-color="${escapeHtml(item.shoeColor || '#FFFFFF')}" data-accent="${escapeHtml(item.accentColor || '#FF3B00')}">
        ${item.imageUrl ? `<img src="${apiUrl('assets/' + item.imageUrl)}" alt="" class="w-full h-full object-contain p-1">` : ''}
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-[9px] font-mono text-neutral-400 uppercase">${escapeHtml(item.brand || '')}</p>
        <p class="text-xs font-bold truncate">${escapeHtml(item.name || '')}</p>
        <p class="text-[10px] text-neutral-500">${escapeHtml(item.priceLabel || '')}</p>
      </div>
      <span class="text-[9px] font-black uppercase tracking-widest text-[#FF3B00]">${ids.length >= 2 ? 'Replace' : 'Add'}</span>
    </button>
  `).join('');

  els.pool.querySelectorAll('[data-compare-add]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const productId = btn.getAttribute('data-compare-add');
      if (!productId) return;
      try {
        const res = await fetch(apiUrl('api/compare.php'), {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: new URLSearchParams({
            csrf_token: getCsrf(),
            ajax: '1',
            mode: 'add',
            product_id: productId,
          }),
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Could not update compare list');

        window.SHOEBOX_COMPARE.active = data.compareIds || activeIds;
        const nextPrimary = comparePool().find(item => item.id === (data.compareIds?.[0] || primary.id)) || primary;
        const nextSecondary = comparePool().find(item => item.id === (data.compareIds?.[1] || 0)) || null;
        renderCompareModal(nextPrimary, nextSecondary, data.compareIds || activeIds);
      } catch (err) {
        showToast('Compare Error', err.message || 'Could not update compare list.', 'warning');
      }
    });
  });
}

function openSizeModal(productId, sizes, form) {
  const modal = document.getElementById('size-modal');
  const grid = document.getElementById('size-grid');
  const confirmBtn = document.getElementById('size-confirm');
  const sizeField = getSizeField(form, productId);
  if (!form) return;
  if (!modal || !grid) {
    if (sizes.length > 0) {
      if (sizeField) sizeField.value = sizes[0];
      form.submit();
    }
    return;
  }

  grid.innerHTML = '';
  let selected = sizes[0];
  sizes.forEach(size => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = 'UK ' + size;
    btn.className = 'border border-[#E8E5DF] px-4 py-2 text-sm font-bold hover:border-black size-btn';
    if (size === selected) btn.classList.add('border-black', 'bg-neutral-900', 'text-white');
    btn.onclick = () => {
      selected = size;
      grid.querySelectorAll('.size-btn').forEach(b => b.classList.remove('border-black', 'bg-neutral-900', 'text-white'));
      btn.classList.add('border-black', 'bg-neutral-900', 'text-white');
    };
    grid.appendChild(btn);
  });

  confirmBtn.onclick = () => {
    if (sizeField) sizeField.value = selected;
    modal.classList.remove('open');
    form.submit();
  };

  modal.classList.add('open');
}

function closeSizeModal() {
  document.getElementById('size-modal')?.classList.remove('open');
}

function getSizeField(form, productId) {
  return document.getElementById('size-' + productId) || form?.querySelector('input[name="size"]') || null;
}

function initHeroCarousels() {
  const stage = document.getElementById('hero-sticky-stage');
  const wrapper = document.getElementById('hero-scroll-wrapper');
  if (!stage || !wrapper) return;

  let slides = [];
  try {
    slides = JSON.parse(stage.dataset.heroSlides || '[]');
  } catch (err) {
    return;
  }

  if (!slides.length) return;

  // Preload all sneaker images to prevent flickering/flashing on scroll
  slides.forEach(slide => {
    if (slide.imageUrl) {
      const img = new Image();
      img.src = apiUrl('assets/' + slide.imageUrl);
    }
  });

  // DOM Elements
  const brandEl = document.getElementById('hero-brand');
  const categoryEl = document.getElementById('hero-category');
  const titleEl = document.getElementById('hero-title');
  const descriptionEl = document.getElementById('hero-description');
  const priceEl = document.getElementById('hero-price');
  const originalPriceEl = document.getElementById('hero-original-price');
  const ratingValEl = document.getElementById('hero-rating-value');
  const stockEl = document.getElementById('hero-stock');
  const linkEl = document.getElementById('hero-link');
  const sneakerImg = document.getElementById('hero-image');
  const sneakerContainer = document.getElementById('hero-sneaker-container');
  const form = document.querySelector('[data-hero-form]');
  const productIdInput = form?.querySelector('input[name="product_id"]');
  const sizeInput = document.getElementById('size-hero');

  const indexCurrentEl = document.getElementById('hero-index-current');
  const progressBar = document.getElementById('hero-scroll-progress-bar');
  const productAccentGlow = document.getElementById('product-accent-glow');

  // Thumbnail buttons
  const thumbButtons = document.querySelectorAll('[data-thumbnail-index]');

  let currentIdx = 0;
  let isTransitioning = false;

  // Setup click listeners for thumbnails
  thumbButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetIndex = parseInt(btn.dataset.thumbnailIndex || '0', 10);
      
      // Calculate scroll Y to snap/align with the product block
      const rect = wrapper.getBoundingClientRect();
      const totalDist = wrapper.offsetHeight - window.innerHeight;
      const scrollPercent = targetIndex / (slides.length - 1);
      
      // Scroll Y relative to document
      const absoluteWrapperTop = window.scrollY + rect.top;
      const targetScrollY = absoluteWrapperTop + (scrollPercent * totalDist);
      
      window.scrollTo({
        top: targetScrollY,
        behavior: 'smooth'
      });
    });
  });

  // Apply visual updates for active product
  const applySlide = (index) => {
    if (index < 0 || index >= slides.length) return;
    const slide = slides[index];

    // Update text content
    if (brandEl) brandEl.textContent = slide.brand || '';
    if (categoryEl) categoryEl.textContent = slide.isExclusive ? 'Kerala Exclusive Drop' : (slide.category || slide.brand || '');
    if (titleEl) titleEl.textContent = slide.name || '';
    if (descriptionEl) descriptionEl.textContent = slide.description || '';
    if (priceEl) priceEl.textContent = slide.priceLabel || '';
    if (ratingValEl) ratingValEl.textContent = slide.ratingLabel || '0.0';

    if (originalPriceEl) {
      if (slide.originalPriceLabel) {
        originalPriceEl.textContent = slide.originalPriceLabel;
        originalPriceEl.classList.remove('hidden');
      } else {
        originalPriceEl.textContent = '';
        originalPriceEl.classList.add('hidden');
      }
    }

    if (stockEl) {
      if (slide.stockLabel) {
        stockEl.textContent = slide.stockLabel;
        stockEl.classList.remove('hidden');
      } else {
        stockEl.textContent = '';
        stockEl.classList.add('hidden');
      }
    }

    // Update Form Action details
    if (productIdInput) productIdInput.value = slide.id;
    if (linkEl) linkEl.href = apiUrl('product.php?id=' + slide.id);
    if (sizeInput) sizeInput.value = ''; // Reset size select for new product

    if (form) {
      form.onsubmit = (event) => {
        event.preventDefault();
        openSizeModal(slide.id, slide.sizes || [], form);
      };
    }

    // Update Sneaker image source and attributes
    if (sneakerImg) {
      if (slide.imageUrl) {
        sneakerImg.src = apiUrl('assets/' + slide.imageUrl);
        sneakerImg.alt = slide.name || '';
        sneakerImg.classList.remove('hidden');
      } else {
        sneakerImg.classList.add('hidden');
      }
    }

    if (sneakerContainer) {
      sneakerContainer.className = `w-full h-80 sm:h-96 flex items-center justify-center ${!slide.imageUrl ? 'sneaker-graphic' : ''}`;
      sneakerContainer.dataset.color = slide.shoeColor || '#FFFFFF';
      sneakerContainer.dataset.accent = slide.accentColor || '#FF3B00';
      sneakerContainer.style.setProperty('--shoe-color', slide.shoeColor || '#FFFFFF');
      sneakerContainer.style.setProperty('--accent-color', slide.accentColor || '#FF3B00');
    }

    // Update index display
    if (indexCurrentEl) {
      indexCurrentEl.textContent = String(index + 1).padStart(2, '0');
    }

    // Dynamic accent glows
    if (productAccentGlow) {
      productAccentGlow.style.color = slide.accentColor || '#FF3B00';
    }

    // Update Thumbnails highlight
    thumbButtons.forEach((btn, btnIdx) => {
      if (btnIdx === index) {
        btn.classList.remove('border-white/10', 'hover:border-white/30');
        btn.classList.add('border-[#FF3B00]');
      } else {
        btn.classList.remove('border-[#FF3B00]');
        btn.classList.add('border-white/10', 'hover:border-white/30');
      }
    });

    currentIdx = index;
  };

  // Passive Scroll loop
  let ticking = false;

  const updateScrollState = () => {
    const rect = wrapper.getBoundingClientRect();
    const totalDist = wrapper.offsetHeight - window.innerHeight;
    
    // Calculate global progress of scroll container: 0 to 1
    let progress = -rect.top / totalDist;
    progress = Math.max(0, Math.min(1, progress));
    
    // Update progress bar
    if (progressBar) {
      progressBar.style.height = `${progress * 100}%`;
    }

    // Calculate which slide we are on
    const fractionalIndex = progress * (slides.length - 1);
    const activeIndex = Math.round(fractionalIndex);

    // Apply 3D tilt & translate rotation based on scroll drift
    const localProgress = fractionalIndex - activeIndex; // -0.5 to 0.5
    const rotation = -8 + (localProgress * -12); // Tilted between -2deg and -14deg
    const translation = localProgress * 28; // translateY drift between -14px and 14px

    if (sneakerImg) {
      sneakerImg.style.setProperty('--sneaker-rotation', `${rotation}deg`);
      sneakerImg.style.setProperty('--sneaker-translation', `${translation}px`);
    }

    // If state index changes, trigger fade transition and content update
    if (activeIndex !== currentIdx && !isTransitioning) {
      isTransitioning = true;
      
      const fadeElements = [
        brandEl, categoryEl, titleEl, descriptionEl, 
        priceEl, originalPriceEl, ratingValEl, stockEl, 
        sneakerImg, linkEl
      ];

      fadeElements.forEach(el => {
        if (el) el.classList.add('fade-out');
      });

      setTimeout(() => {
        applySlide(activeIndex);
        fadeElements.forEach(el => {
          if (el) el.classList.remove('fade-out');
        });
        isTransitioning = false;
      }, 150); // Mapped to transition timing
    }

    ticking = false;
  };

  const onScroll = () => {
    if (!ticking) {
      window.requestAnimationFrame(updateScrollState);
      ticking = true;
    }
  };

  window.addEventListener('scroll', onScroll, { passive: true });

  // Initial call
  applySlide(0);
  updateScrollState();
}
