<div
  x-data="{ open:false }"
  @open-cart.window="open = true; document.body.classList.add('overflow-hidden')"
  @keydown.escape.window="open = false; document.body.classList.remove('overflow-hidden')"
  x-cloak
  class="fixed inset-0 z-50 pointer-events-none"
  aria-live="polite"
>
  <!-- Backdrop -->
  <div
    x-show="open"
    x-transition.opacity
    @click="open=false; document.body.classList.remove('overflow-hidden')"
    class="absolute inset-0 bg-black/40 pointer-events-auto"
    aria-hidden="true"
  ></div>

  <!-- Panel -->
  <aside
    x-show="open"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="translate-x-full opacity-0"
    x-transition:enter-end="translate-x-0 opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="translate-x-0 opacity-100"
    x-transition:leave-end="translate-x-full opacity-0"
    class="absolute right-0 top-0 h-full w-full max-w-sm bg-white dark:bg-gray-800 shadow-2xl pointer-events-auto flex flex-col"
    role="dialog"
    aria-modal="true"
    aria-label="Panier"
  >
    <!-- Header -->
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Votre panier</h2>
        <span class="inline-flex items-center justify-center text-xs px-1.5 py-0.5 rounded-full bg-gray-900 text-white"
              id="cart-badge-drawer">
          {{ session('cart_count', 0) }}
        </span>
      </div>
      <button
        class="w-8 h-8 rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center justify-center"
        @click="open=false; document.body.classList.remove('overflow-hidden')"
        aria-label="Fermer"
      >✕</button>
    </div>

    <!-- Body (mini résumé statique pour éviter les requêtes lourdes) -->
    <div class="p-4 grow overflow-y-auto text-sm text-gray-700 dark:text-gray-200 space-y-3">
      <p class="text-gray-600 dark:text-gray-300">
        Consultez le détail du panier, ajustez les quantités et finalisez la commande sur la page dédiée.
      </p>

      <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
        <div class="flex items-center justify-between">
          <span>Articles</span>
          <span id="cart-count-inline" class="font-medium">{{ session('cart_count', 0) }}</span>
        </div>
        <p class="mt-2 text-xs text-gray-500">
          Le total s’affichera sur la page panier.
        </p>
      </div>

      <div class="pt-2">
        <a href="{{ route('cart.show') }}"
           class="w-full inline-flex items-center justify-center px-4 py-2 rounded bg-gray-900 text-white hover:opacity-90">
          Voir le panier
        </a>
      </div>
    </div>

    <!-- Footer actions -->
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
      @auth
        <a href="{{ route('checkout.show') }}"
           class="w-full inline-flex items-center justify-center px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700">
          Passer au paiement
        </a>
      @else
        <a href="{{ route('login') }}"
           class="w-full inline-flex items-center justify-center px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
          Se connecter pour payer
        </a>
      @endauth
      <button
        class="mt-2 w-full inline-flex items-center justify-center px-4 py-2 rounded border"
        @click="open=false; document.body.classList.remove('overflow-hidden')"
      >
        Continuer vos achats
      </button>
    </div>
  </aside>
</div>