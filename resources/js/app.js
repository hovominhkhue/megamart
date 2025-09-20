import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// ---------- Utils ----------
function qs(sel, root = document) { return root.querySelector(sel); }
function getCsrf() {
  const el = qs('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

function showToast(msg, ok = true) {
  const t = qs('#toast');
  if (!t) return;
  t.textContent = msg;
  t.classList.remove('hidden');
  t.style.background = ok ? 'black' : '#b91c1c'; // rouge-700
  // Accessibilité: announce
  t.setAttribute('role', 'status');
  t.setAttribute('aria-live', 'polite');
  window.clearTimeout(showToast._tid);
  showToast._tid = window.setTimeout(() => t.classList.add('hidden'), 1800);
}

function updateCartBadge(count) {
  ['cart-badge', 'cart-badge-drawer', 'cart-count-inline'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = count;
  });
}

// ---------- Add-to-cart (fetch) ----------
document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.add-to-cart-btn');
  if (!btn) return;

  // déjà désactivé / pas de stock
  if (btn.disabled || Number(btn.dataset.stock) === 0) return;

  // quantité (optionnelle)
  const qtySelector = btn.dataset.qtySelector;
  const qtyInput = qtySelector ? qs(qtySelector) : null;
  const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value || '1', 10)) : 1;

  // Prépare la requête
  const url = btn.dataset.url;
  const fd = new FormData();
  fd.append('qty', qty);

  btn.disabled = true;
  btn.classList.add('opacity-60');
  const originalLabel = btn.textContent;

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': getCsrf(),
        'Accept': 'application/json',
      },
      body: fd
    });

    // Tente JSON, même si res.ok === false pour lire le message
    let data = null;
    try { data = await res.json(); } catch (_err) {}

    if (!res.ok) {
      const msg = (data && data.message) ? data.message : `Erreur ${res.status}`;
      showToast(msg, false);
      return;
    }

    // Succès
    const count = data && typeof data.cart_count !== 'undefined' ? data.cart_count : null;
    if (count !== null) updateCartBadge(count);

    // Option: ouvrir le drawer après ajout
    // window.dispatchEvent(new CustomEvent('open-cart'));

    showToast((data && data.message) || 'Ajouté au panier', true);

    // Option UX: remettre la quantité à 1 si un input est présent
    if (qtyInput) qtyInput.value = '1';
  } catch (err) {
    console.error(err);
    showToast("Impossible d'ajouter l'article. Vérifiez votre connexion.", false);
  } finally {
    btn.disabled = false;
    btn.classList.remove('opacity-60');
    btn.textContent = originalLabel;
  }
});