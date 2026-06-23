(function () {
  function csrfToken() {
    return window.SHOEBOX_ADMIN?.csrf || document.querySelector('meta[name="csrf-token"]')?.content || '';
  }

  function setBodySidebar(open) {
    document.body.classList.toggle('sidebar-open', open);
  }

  function closePanels() {
    document.querySelector('[data-profile-panel]')?.setAttribute('hidden', '');
    document.querySelector('[data-alert-panel]')?.setAttribute('hidden', '');
  }

  function togglePanel(triggerSelector, panelSelector) {
    const trigger = document.querySelector(triggerSelector);
    const panel = document.querySelector(panelSelector);
    if (!trigger || !panel) return;

    trigger.addEventListener('click', (event) => {
      event.stopPropagation();
      const hidden = panel.hasAttribute('hidden');
      closePanels();
      if (hidden) panel.removeAttribute('hidden');
    });
  }

  function bindSearch() {
    const input = document.querySelector('[data-admin-search]');
    if (!input) return;

    input.addEventListener('input', () => {
      const query = input.value.trim().toLowerCase();
      document.querySelectorAll('table.admin-table tbody tr').forEach((row) => {
        const text = row.textContent.toLowerCase();
        row.style.display = !query || text.includes(query) ? '' : 'none';
      });
    });
  }

  function bindSortableTables() {
    document.querySelectorAll('table.admin-table th[data-sort]').forEach((header) => {
      header.addEventListener('click', () => {
        const table = header.closest('table');
        const tbody = table?.querySelector('tbody');
        if (!tbody) return;
        const index = Array.from(header.parentNode.children).indexOf(header);
        const direction = header.dataset.direction === 'asc' ? 'desc' : 'asc';
        header.dataset.direction = direction;
        const rows = Array.from(tbody.querySelectorAll('tr')).sort((a, b) => {
          const av = a.children[index]?.textContent.trim().toLowerCase() || '';
          const bv = b.children[index]?.textContent.trim().toLowerCase() || '';
          const aNum = parseFloat(av.replace(/[^\d.-]/g, ''));
          const bNum = parseFloat(bv.replace(/[^\d.-]/g, ''));
          const numeric = !Number.isNaN(aNum) && !Number.isNaN(bNum);
          if (numeric) {
            return direction === 'asc' ? aNum - bNum : bNum - aNum;
          }
          return direction === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
        });
        rows.forEach((row) => tbody.appendChild(row));
      });
    });
  }

  function bindForms() {
    document.querySelectorAll('form[data-admin-validate]').forEach((form) => {
      form.addEventListener('submit', (event) => {
        const requiredFields = form.querySelectorAll('[required]');
        let firstInvalid = null;
        requiredFields.forEach((field) => {
          const value = (field.value || '').trim();
          if (!value && !firstInvalid) {
            firstInvalid = field;
          }
        });

        const fileInput = form.querySelector('input[type="file"]');
        if (fileInput?.files?.[0]) {
          const allowed = ['image/jpeg', 'image/png', 'image/webp'];
          if (!allowed.includes(fileInput.files[0].type)) {
            event.preventDefault();
            alert('Please upload JPG, PNG, or WEBP images only.');
            return;
          }
        }

        if (firstInvalid) {
          event.preventDefault();
          firstInvalid.focus();
          alert('Please complete the required fields before submitting.');
        }
      });
    });
  }

  function bindSidebar() {
    document.querySelectorAll('[data-sidebar-open]').forEach((button) => {
      button.addEventListener('click', () => setBodySidebar(true));
    });
    document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
      button.addEventListener('click', () => setBodySidebar(false));
    });
  }

  function bindAddRowButtons() {
    document.querySelectorAll('[data-add-row]').forEach((button) => {
      button.addEventListener('click', () => {
        const targetSelector = button.dataset.addRow;
        const template = document.querySelector(targetSelector);
        const container = document.querySelector(button.dataset.target);
        if (!template || !container) return;
        const node = template.content.cloneNode(true);
        container.appendChild(node);
      });
    });
  }

  function bindInlineActions() {
    document.querySelectorAll('[data-inline-action]').forEach((button) => {
      button.addEventListener('click', () => {
        const label = button.dataset.inlineActionLabel || 'Action completed.';
        alert(label);
      });
    });
  }

  document.addEventListener('click', (event) => {
    if (!event.target.closest('.profile-menu') && !event.target.closest('.alert-button')) {
      closePanels();
    }
  });

  document.addEventListener('DOMContentLoaded', () => {
    bindSidebar();
    togglePanel('[data-profile-toggle]', '[data-profile-panel]');
    togglePanel('[data-alert-toggle]', '[data-alert-panel]');
    bindSearch();
    bindSortableTables();
    bindForms();
    bindAddRowButtons();
    bindInlineActions();
    window.ADMIN_CSRF = csrfToken();
  });
})();
