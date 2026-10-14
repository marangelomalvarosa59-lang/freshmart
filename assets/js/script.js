// Handle "Add to Cart" buttons using Fetch API (no page reload)
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.add-to-cart-btn');
  if (!btn) return;

  const productId = btn.dataset.id;
  const productName = btn.dataset.name;
  const originalText = btn.textContent;

  btn.disabled = true;
  btn.textContent = 'Adding...';

  fetch('/cart_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=add&product_id=${encodeURIComponent(productId)}&quantity=1`
  })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      btn.textContent = originalText;

      if (data.success) {
        showToast(data.message || (productName + ' added to cart!'));
        const cartCountEl = document.getElementById('cart-count');
        if (cartCountEl) cartCountEl.textContent = data.cart_count;
      } else {
        showToast(data.message || 'Something went wrong.', true);
      }
    })
    .catch(() => {
      btn.disabled = false;
      btn.textContent = originalText;
      showToast('Network error. Please try again.', true);
    });
});

function showToast(message, isError = false) {
  const toast = document.getElementById('toast');
  if (!toast) return;
  toast.textContent = message;
  toast.classList.remove('hidden', 'bg-gray-900', 'bg-red-600');
  toast.classList.add(isError ? 'bg-red-600' : 'bg-gray-900');
  toast.style.opacity = '1';

  clearTimeout(window._toastTimeout);
  window._toastTimeout = setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => toast.classList.add('hidden'), 300);
  }, 2500);
}

// Cart page: quantity update + remove via AJAX
document.addEventListener('click', function (e) {
  const removeBtn = e.target.closest('.cart-remove-btn');
  if (removeBtn) {
    const row = removeBtn.closest('.cart-row');
    const productId = removeBtn.dataset.id;

    fetch('/cart_action.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=remove&product_id=${encodeURIComponent(productId)}`
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          row.remove();
          updateCartUI(data);
        }
      });
  }
});

document.addEventListener('change', function (e) {
  const qtyInput = e.target.closest('.cart-qty-input');
  if (qtyInput) {
    const productId = qtyInput.dataset.id;
    const price = parseFloat(qtyInput.dataset.price);
    let quantity = parseInt(qtyInput.value, 10);
    if (isNaN(quantity) || quantity < 1) quantity = 1;
    qtyInput.value = quantity;

    fetch('/cart_action.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `action=update&product_id=${encodeURIComponent(productId)}&quantity=${quantity}`
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const subtotalEl = document.getElementById('subtotal-' + productId);
          if (subtotalEl) subtotalEl.textContent = '₱' + (price * quantity).toFixed(2);
          updateCartUI(data);
        }
      });
  }
});

function updateCartUI(data) {
  const cartCountEl = document.getElementById('cart-count');
  if (cartCountEl) cartCountEl.textContent = data.cart_count;
  const cartTotalEl = document.getElementById('cart-total');
  if (cartTotalEl) cartTotalEl.textContent = '₱' + data.cart_total;

  if (data.cart_count === 0) {
    const emptyState = document.getElementById('empty-cart-state');
    const cartTable = document.getElementById('cart-table-wrapper');
    if (emptyState && cartTable) {
      cartTable.classList.add('hidden');
      emptyState.classList.remove('hidden');
    }
  }
}
